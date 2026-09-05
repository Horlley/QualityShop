<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->session()->has('checkout_key')) {
            $request->session()->put('checkout_key', (string) Str::uuid());
        }

        return view('cart.index', $this->cartViewData($request));
    }

    public function store(AddCartItemRequest $request): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $quantity = $request->integer('quantity');

        if (! $product->active) {
            return back()->withErrors(['product_id' => 'Este produto não está disponível.']);
        }

        $cart = $request->session()->get('cart', []);
        $newQuantity = ((int) ($cart[$product->id] ?? 0)) + $quantity;

        if ($newQuantity > min(10, $product->stock)) {
            return back()->withErrors([
                'quantity' => "A quantidade solicitada ultrapassa o limite ou o estoque de {$product->name}.",
            ]);
        }

        $cart[$product->id] = $newQuantity;
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', "{$product->name} foi adicionado ao carrinho.");
    }

    public function update(UpdateCartItemRequest $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);

        if (! array_key_exists($product->id, $cart)) {
            abort(404);
        }

        $quantity = $request->integer('quantity');

        if (! $product->active || $quantity > $product->stock) {
            return back()->withErrors([
                'quantity' => "Existem somente {$product->stock} unidades disponíveis de {$product->name}.",
            ]);
        }

        $cart[$product->id] = $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Quantidade atualizada.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        if ($cart === []) {
            $request->session()->forget('coupon_code');
        }

        return back()->with('success', 'Produto removido do carrinho.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            ['coupon_code' => ['required', 'string', 'max:20']],
            ['coupon_code.required' => 'Informe um cupom.'],
        );
        $code = mb_strtoupper(trim($validated['coupon_code']));

        if ($code !== 'QA10') {
            return back()->withErrors(['coupon_code' => 'Cupom inválido ou inativo.']);
        }

        $summary = $this->cartViewData($request);
        if ($summary['actualSubtotal'] < 100) {
            return back()->withErrors(['coupon_code' => 'O cupom QA10 exige subtotal mínimo de R$ 100,00.']);
        }

        $request->session()->put('coupon_code', $code);

        return back()->with('success', 'Cupom QA10 aplicado: 10% de desconto.');
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget('coupon_code');

        return back()->with('success', 'Cupom removido.');
    }

    /** @return array<string, mixed> */
    private function cartViewData(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::query()
            ->whereIn('id', array_keys($cart))
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $validCart = [];
        $items = new Collection;
        $actualSubtotal = 0.0;
        $displayedSubtotal = 0.0;
        $staleTotalScenario = (bool) $request->session()->get('lab_cart_total_stale', false);

        foreach ($cart as $productId => $quantity) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }

            $quantity = (int) $quantity;
            $lineTotal = round((float) $product->price * $quantity, 2);
            $displayedLineTotal = $staleTotalScenario && $quantity > 1
                ? (float) $product->price
                : $lineTotal;

            $validCart[$product->id] = $quantity;
            $actualSubtotal += $lineTotal;
            $displayedSubtotal += $displayedLineTotal;
            $items->push([
                'product' => $product,
                'quantity' => $quantity,
                'lineTotal' => $displayedLineTotal,
            ]);
        }

        $request->session()->put('cart', $validCart);
        $couponCode = $request->session()->get('coupon_code');
        if ($actualSubtotal < 100) {
            $request->session()->forget('coupon_code');
            $couponCode = null;
        }
        $discount = $couponCode === 'QA10' && $displayedSubtotal >= 100
            ? round($displayedSubtotal * 0.10, 2)
            : 0.0;

        return [
            'items' => $items,
            'customers' => $request->user()->isStaff() ? User::query()->where('role', 'customer')->where('active', true)->orderBy('name')->get(['id', 'name']) : collect(),
            'subtotal' => round($displayedSubtotal, 2),
            'actualSubtotal' => round($actualSubtotal, 2),
            'discount' => $discount,
            'total' => round($displayedSubtotal - $discount, 2),
            'couponCode' => $couponCode,
            'staleTotalScenario' => $staleTotalScenario,
        ];
    }
}
