<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CancelledOrderController extends Controller
{
    public function store(Request $request, Order $order, OrderLifecycle $lifecycle): RedirectResponse
    {
        $request->validate(['simulate_refund_failure' => ['sometimes', 'boolean']]);
        $lifecycle->transition($order, $request->user(), 'cancel', $request->boolean('simulate_refund_failure'));

        return redirect()->route('orders.show', $order)->with('success', 'Cancelamento confirmado. Estoque devolvido e estorno simulado registrado, quando houve pagamento.');
    }
}
