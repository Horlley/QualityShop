<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LabScenarioController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(['active' => ['required', 'boolean']]);
        $request->session()->put('lab_cart_total_stale', (bool) $validated['active']);

        $message = $validated['active']
            ? 'Cenário QS-BUG-01 ativado. Altere uma quantidade e investigue o total.'
            : 'Cenário controlado desativado. O carrinho voltou ao comportamento correto.';

        return redirect()->route('cart.index')->with('success', $message);
    }
}
