<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(Request $request, OrderLifecycle $lifecycle): RedirectResponse
    {
        $validated = $request->validate([
            'checkout_key' => ['required', 'uuid'],
            'payment_result' => ['sometimes', 'in:approved,pending,declined,timeout'],
            'customer_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'customer')->where('active', true)],
        ]);
        $key = $validated['checkout_key'];
        abort_if(! $request->user()->isStaff() && $request->filled('customer_id'), 403);
        $customerId = $validated['customer_id'] ?? $request->user()->id;
        $existing = Order::query()->where('checkout_key', $key)->where('created_by_id', $request->user()->id)->first();
        if ($existing) {
            return redirect()->route('orders.show', $existing)->with('success', 'Esta compra já foi registrada. Nenhuma nova cobrança ou baixa de estoque.');
        }
        abort_unless(hash_equals((string) $request->session()->get('checkout_key'), $key), 419);
        $cart = $request->session()->get('cart', []);
        if ($cart === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'O carrinho está vazio.']);
        }
        $order = DB::transaction(function () use ($request, $cart, $key, $validated, $lifecycle, $customerId): Order {
            $existing = Order::query()->where('checkout_key', $key)->where('created_by_id', $request->user()->id)->first();
            if ($existing) {
                return $existing;
            }
            $products = Product::query()->whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
            $subtotalCents = 0;
            foreach ($cart as $id => $quantity) {
                $product = $products->get($id);
                if (! $product || ! $product->active || $quantity < 1 || $quantity > 10 || $quantity > $product->stock) {
                    throw ValidationException::withMessages(['cart' => 'Item indisponível ou estoque insuficiente. Revise o carrinho.']);
                }
                $subtotalCents += (int) round((float) $product->price * 100) * (int) $quantity;
            }
            $discountCents = $request->session()->get('coupon_code') === 'QA10' && $subtotalCents >= 10000
                ? (int) round($subtotalCents / 10) : 0;
            $order = Order::query()->create([
                'user_id' => $customerId, 'number' => 'QS-'.Str::upper((string) Str::ulid()),
                'created_by_id' => $request->user()->id,
                'checkout_key' => $key, 'status' => 'created', 'payment_status' => 'pending',
                'subtotal' => $subtotalCents / 100, 'discount' => $discountCents / 100,
                'coupon_code' => $discountCents > 0 ? 'QA10' : null, 'total' => ($subtotalCents - $discountCents) / 100,
            ]);
            foreach ($cart as $id => $quantity) {
                $product = $products->get($id);
                $order->items()->create([
                    'product_id' => $id, 'sku' => $product->sku, 'name' => $product->name,
                    'unit_price' => $product->price, 'quantity' => $quantity,
                    'line_total' => (int) round((float) $product->price * 100) * $quantity / 100,
                ]);
                $updated = Product::query()->whereKey($id)->where('active', true)->where('stock', '>=', $quantity)->decrement('stock', $quantity);
                if ($updated !== 1) {
                    throw ValidationException::withMessages(['cart' => 'Estoque alterado por outra compra. Tente novamente.']);
                }
            }
            $lifecycle->record($order, $request->user(), 'created', 'Pedido criado e estoque reservado. Frete didático gratuito.');
            $result = $validated['payment_result'] ?? 'approved';
            if ($result !== 'pending') {
                return $lifecycle->transition($order, $request->user(), match ($result) {
                    'approved' => 'approve', 'declined' => 'decline', default => 'timeout'
                });
            }

            return $order;
        }, 3);
        $request->session()->forget(['cart', 'coupon_code', 'lab_cart_total_stale', 'checkout_key']);

        return redirect()->route('orders.show', $order)->with('success', 'Pedido registrado. Consulte o resultado do pagamento simulado e o histórico.');
    }
}
