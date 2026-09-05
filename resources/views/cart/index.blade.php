@extends('layouts.app')

@section('title', 'Carrinho')

@section('content')
    <header class="page-heading compact">
        <div><p class="section-label">Carrinho</p><h1>Revise dados, limites e valores.</h1></div>
        <a class="button button-secondary button-small" href="{{ route('catalog.index') }}">Continuar comprando</a>
    </header>

    <section @class(['scenario-panel', 'scenario-active' => $staleTotalScenario])>
        <div>
            <span class="mission-code">CENÁRIO CONTROLADO QS-BUG-01</span>
            <h2>Missão de investigação do carrinho</h2>
            <p>{{ $staleTotalScenario ? 'Cenário ativo nesta sessão. Explore quantidades, registre esperado e observado e compare com o modo normal.' : 'Desativado por padrão. Ative somente quando chegar ao exercício de investigação de defeitos.' }}</p>
        </div>
        <form method="POST" action="{{ route('lab.cart-scenario.update') }}">
            @csrf
            <input type="hidden" name="active" value="{{ $staleTotalScenario ? 0 : 1 }}">
            <button class="button {{ $staleTotalScenario ? 'button-danger' : 'button-secondary' }} button-small" type="submit">{{ $staleTotalScenario ? 'Desativar cenário' : 'Ativar cenário' }}</button>
        </form>
    </section>

    @if($items->isEmpty())
        <div class="empty-state"><h2>Seu carrinho está vazio</h2><p>Adicione produtos para praticar quantidade, estoque, cupom e checkout.</p><a class="button" href="{{ route('catalog.index') }}">Abrir catálogo</a></div>
    @else
        <div class="cart-layout">
            <section class="panel table-panel" aria-label="Itens do carrinho">
                <div class="table-scroll"><table><thead><tr><th>Produto</th><th>Preço</th><th>Quantidade</th><th>Total</th><th></th></tr></thead><tbody>
                @foreach($items as $item)
                    <tr>
                        <td><strong>{{ $item['product']->name }}</strong><small>{{ $item['product']->sku }} · estoque {{ $item['product']->stock }}</small></td>
                        <td>R$ {{ number_format((float) $item['product']->price, 2, ',', '.') }}</td>
                        <td><form class="quantity-form" method="POST" action="{{ route('cart.items.update', $item['product']) }}">@csrf @method('PATCH')<input aria-label="Quantidade de {{ $item['product']->name }}" name="quantity" type="number" value="{{ $item['quantity'] }}" min="1" max="10"><button class="icon-button" type="submit">Atualizar</button></form></td>
                        <td><strong>R$ {{ number_format($item['lineTotal'], 2, ',', '.') }}</strong></td>
                        <td><form method="POST" action="{{ route('cart.items.destroy', $item['product']) }}">@csrf @method('DELETE')<button class="link-button danger" type="submit">Remover</button></form></td>
                    </tr>
                @endforeach
                </tbody></table></div>
            </section>

            <aside class="panel summary-card">
                <p class="section-label">Resumo</p>
                <dl><div><dt>Subtotal</dt><dd>R$ {{ number_format($subtotal, 2, ',', '.') }}</dd></div><div><dt>Desconto</dt><dd>- R$ {{ number_format($discount, 2, ',', '.') }}</dd></div><div class="summary-total"><dt>Total</dt><dd>R$ {{ number_format($total, 2, ',', '.') }}</dd></div></dl>
                @if($couponCode)
                    <div class="coupon-applied"><strong>QA10 aplicado</strong><form method="POST" action="{{ route('cart.coupon.destroy') }}">@csrf @method('DELETE')<button class="link-button" type="submit">Remover</button></form></div>
                @else
                    <form class="coupon-form" method="POST" action="{{ route('cart.coupon.store') }}">@csrf<label for="coupon_code">Cupom</label><div><input id="coupon_code" name="coupon_code" placeholder="Digite QA10"><button class="button button-small" type="submit">Aplicar</button></div><small>10% para subtotal a partir de R$ 100,00.</small></form>
                @endif
                <form method="POST" action="{{ route('checkout.store') }}">@csrf
                    <input type="hidden" name="checkout_key" value="{{ session('checkout_key') }}">
                    @if(auth()->user()->isStaff())
                        <label for="customer_id">Registrar para</label><select id="customer_id" name="customer_id"><option value="">Minha conta</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select>
                    @endif
                    <label for="payment_result">Resultado do pagamento simulado</label><select id="payment_result" name="payment_result"><option value="approved">Aprovar</option><option value="pending">Deixar pendente</option><option value="declined">Recusar</option><option value="timeout">Simular timeout</option></select>
                    <p class="form-note">Frete gratuito neste laboratório.</p><button class="button button-full" type="submit">Finalizar compra</button>
                </form>
                <p class="form-note">O pagamento é uma simulação local. Nenhum dado financeiro é solicitado ou transmitido.</p>
            </aside>
        </div>
    @endif
@endsection
