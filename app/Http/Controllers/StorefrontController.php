<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('welcome', [
            'activeProducts' => Product::query()->where('active', true)->count(),
            'completedOrders' => Order::query()->count(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();

        $products = Product::query()
            ->where('active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('name')
            ->get();

        return view('catalog.index', [
            'products' => $products,
            'categories' => Product::query()->where('active', true)->orderBy('category')->distinct()->pluck('category'),
            'search' => $search,
            'category' => $category,
        ]);
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $ordersQuery = Order::query()->with('user')->latest('id');

        if (! $user->canViewAllOrders()) {
            $ordersQuery->whereBelongsTo($user);
        }

        return view('dashboard', [
            'recentOrders' => (clone $ordersQuery)->limit(5)->get(),
            'ordersCount' => (clone $ordersQuery)->count(),
            'activeProducts' => Product::query()->where('active', true)->count(),
            'lowStockProducts' => Product::query()->where('active', true)->where('stock', '<=', 5)->count(),
            'salesTotal' => (float) (clone $ordersQuery)->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->sum('total'),
        ]);
    }
}
