<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderLifecycle
{
    public function transition(Order $order, User $actor, string $action, bool $refundFailure = false): Order
    {
        return DB::transaction(function () use ($order, $actor, $action, $refundFailure): Order {
            $current = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            abort_unless($actor->isStaff() || $current->user_id === $actor->id, 404);

            if ($action === 'cancel') {
                if (! $current->canBeCancelled()) {
                    throw ValidationException::withMessages(['order' => 'Este pedido não pode ser cancelado no estado atual.']);
                }
                if ($current->paid_at && $refundFailure) {
                    throw ValidationException::withMessages(['order' => 'Estorno simulado indisponível. Pedido e estoque foram preservados; tente novamente.']);
                }
                foreach ($current->items as $item) {
                    if ($item->product_id) {
                        Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
                    }
                }
                $current->update([
                    'status' => 'cancelled', 'cancelled_at' => now(),
                    'payment_status' => $current->paid_at ? 'refunded' : 'cancelled',
                    'refunded_at' => $current->paid_at ? now() : null,
                ]);
                $this->record($current, $actor, 'cancelled', 'Cancelamento confirmado. Estoque devolvido uma única vez.');
                if ($current->paid_at) {
                    $this->record($current, $actor, 'refunded', 'Estorno simulado aprovado.', $current->total);
                }

                return $current;
            }

            if (in_array($action, ['approve', 'decline', 'timeout'], true)) {
                if ($action === 'approve' && $current->paid_at) {
                    return $current;
                }
                if ($current->status !== 'created') {
                    throw ValidationException::withMessages(['order' => 'Este pedido não está aguardando pagamento.']);
                }
                $approved = $action === 'approve';
                $current->update([
                    'status' => $approved ? 'paid' : 'created',
                    'payment_status' => match ($action) {
                        'approve' => 'approved', 'decline' => 'declined', default => 'pending'
                    },
                    'paid_at' => $approved ? now() : null,
                ]);
                $description = match ($action) {
                    'approve' => 'Pagamento simulado aprovado. Nenhuma cobrança real.',
                    'decline' => 'Pagamento simulado recusado. É possível tentar novamente.',
                    default => 'Timeout simulado. Pagamento permanece pendente, sem cobrança.',
                };
                $this->record($current, $actor, $action, $description, $approved ? $current->total : null);

                return $current;
            }

            abort_unless($actor->isStaff(), 403);
            $next = match ($action) {
                'process' => ['paid', 'processing'], 'ship' => ['processing', 'shipped'], 'deliver' => ['shipped', 'delivered'], default => null
            };
            if (! $next || $current->status !== $next[0]) {
                throw ValidationException::withMessages(['order' => 'Transição não permitida. Siga: pago → em separação → enviado → entregue.']);
            }
            $current->update(['status' => $next[1]]);
            $this->record($current, $actor, $next[1], 'Pedido alterado para '.$current->statusLabel().'.');

            return $current;
        }, 3);
    }

    public function record(Order $order, User $actor, string $event, string $description, ?string $amount = null): void
    {
        $order->events()->create(['user_id' => $actor->id, 'event' => $event, 'description' => $description, 'amount' => $amount]);
    }
}
