<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderTransitionController extends Controller
{
    public function store(Request $request, Order $order, OrderLifecycle $lifecycle): RedirectResponse
    {
        $validated = $request->validate(['action' => ['required', 'in:approve,decline,timeout,process,ship,deliver']]);
        $lifecycle->transition($order, $request->user(), $validated['action']);

        return redirect()->route('orders.show', $order)->with('success', 'Solicitação processada. Confira o estado e o histórico do pedido.');
    }
}
