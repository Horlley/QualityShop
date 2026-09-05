<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        return view('stock', ['products' => Product::query()->orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['stock' => ['required', 'integer', 'min:0', 'max:100000'], 'previous_stock' => ['required', 'integer', 'min:0']]);
        $updated = Product::query()->whereKey($product->id)->where('stock', $validated['previous_stock'])->update(['stock' => $validated['stock']]);
        if ($updated !== 1) {
            return back()->withErrors(['stock' => 'O estoque mudou desde a consulta. Atualize a página antes de ajustar.']);
        }
        Log::info('Ajuste de estoque', ['actor_id' => $request->user()->id, 'product_id' => $product->id, 'before' => $validated['previous_stock'], 'after' => $validated['stock']]);

        return redirect()->route('stock.index')->with('success', 'Estoque ajustado. O registro está em storage/logs/laravel.log.');
    }
}
