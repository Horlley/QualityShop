<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['user', 'items'])
            ->when(! $request->user()->isStaff(), fn ($query) => $query->whereBelongsTo($request->user()))
            ->latest('id')
            ->get();

        return view('orders.index', ['orders' => $orders]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function show(Request $request, Order $order): View
    {
        if (! $request->user()->isStaff() && $order->user_id !== $request->user()->id) {
            abort(404);
        }

        $order->load(['user', 'items', 'events.user']);

        return view('orders.show', ['order' => $order]);
    }
}
