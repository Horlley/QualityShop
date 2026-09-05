<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancelledOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LabScenarioController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTransitionController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/catalogo', [StorefrontController::class, 'catalog'])->name('catalog.index');
Route::view('/laboratorio', 'laboratory')->name('lab.index');

Route::middleware('guest')->group(function (): void {
    Route::get('/entrar', [AuthController::class, 'create'])->name('login');
    Route::post('/entrar', [AuthController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/sair', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/painel', [StorefrontController::class, 'dashboard'])->name('dashboard');

    Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrinho/itens', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/carrinho/itens/{product}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('/carrinho/itens/{product}', [CartController::class, 'destroy'])->name('cart.items.destroy');
    Route::post('/carrinho/cupom', [CartController::class, 'applyCoupon'])->name('cart.coupon.store');
    Route::delete('/carrinho/cupom', [CartController::class, 'removeCoupon'])->name('cart.coupon.destroy');

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pedidos/{order}/transicao', [OrderTransitionController::class, 'store'])->name('orders.transition');
    Route::post('/pedidos/{order}/cancelamento', [CancelledOrderController::class, 'store'])->name('orders.cancellation.store');

    Route::post('/laboratorio/cenario-carrinho', [LabScenarioController::class, 'update'])->name('lab.cart-scenario.update');
    Route::middleware('role:operator,admin')->group(function (): void {
        Route::resource('users', UserManagementController::class)->only(['index', 'create', 'store']);
    });
    Route::middleware('role:manager,admin')->group(function (): void {
        Route::get('/estoque', [StockController::class, 'index'])->name('stock.index');
        Route::patch('/estoque/{product}', [StockController::class, 'update'])->name('stock.update');
    });
    Route::middleware('role:admin')->group(function (): void {
        Route::resource('users', UserManagementController::class)->only(['edit', 'update']);
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::resource('products', AdminProductController::class)->except(['show', 'destroy']);
    });
});
