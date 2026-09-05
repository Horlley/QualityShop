@extends('layouts.app')

@section('title', 'Pedido '.$order->number)

@section('content')
    <header class="page-heading compact">
        <div><p class="section-label">Detalhes do pedido #{{ $order->id }}</p><h1 class="order-number">{{ $order->number }}</h1><p>Cliente: {{ $order->user->name }} · criado em {{ $order->created_at->format('d/m/Y H:i') }}</p></div>
        <b class="status status-large status-{{ $order->status }}">{{ $order->statusLabel() }}</b>
    </header>
    <div class="order-detail-grid">
        <section class="panel table-panel"><div class="panel-heading"><div><p class="section-label">Evidência</p><h2>Itens registrados</h2></div></div><div class="table-scroll"><table><thead><tr><th>SKU</th><th>Produto</th><th>Quantidade</th><th>Preço</th><th>Total</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td><code>{{ $item->sku }}</code></td><td>{{ $item->name }}</td><td>{{ $item->quantity }}</td><td>R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td><td><strong>R$ {{ number_format((float) $item->line_total, 2, ',', '.') }}</strong></td></tr>@endforeach</tbody></table></div></section>
        <aside class="panel summary-card"><p class="section-label">Valores persistidos</p><dl><div><dt>Subtotal</dt><dd>R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</dd></div><div><dt>Cupom</dt><dd>{{ $order->coupon_code ?? 'Nenhum' }}</dd></div><div><dt>Desconto</dt><dd>- R$ {{ number_format((float) $order->discount, 2, ',', '.') }}</dd></div><div class="summary-total"><dt>Total</dt><dd>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</dd></div></dl>
            @if($order->canBeCancelled())<form method="POST" action="{{ route('orders.cancellation.store', $order) }}">@csrf<button class="button button-danger button-full" type="submit">Cancelar pedido</button></form><p class="form-note">QS-19: o cancelamento restaura o estoque e não pode ser repetido.</p>@elseif($order->status === 'cancelled')<div class="cancel-note">Cancelado em {{ $order->cancelled_at?->format('d/m/Y H:i') }}. O estoque foi devolvido.</div>@endif
        </aside>
    </div>
    <section class="panel form-panel lifecycle-panel">
        <p class="section-label">Pagamento e operação · simulação</p>
        <h2>Próximo passo do pedido</h2>
        <p>Pagamento: <strong>{{ ['pending' => 'Pendente', 'approved' => 'Aprovado', 'declined' => 'Recusado', 'refunded' => 'Estornado', 'cancelled' => 'Cancelado'][$order->payment_status] ?? $order->payment_status }}</strong>. Nenhuma cobrança real.</p>
        @if($order->status === 'created')
            <form method="POST" class="action-row" action="{{ route('orders.transition', $order) }}">@csrf<button class="button button-small" name="action" value="approve">Aprovar pagamento</button><button class="button button-secondary button-small" name="action" value="decline">Recusar pagamento</button><button class="button button-secondary button-small" name="action" value="timeout">Simular timeout</button></form>
        @endif
        @if(auth()->user()->isStaff() && in_array($order->status, ['paid', 'processing', 'shipped']))
            <form method="POST" action="{{ route('orders.transition', $order) }}">@csrf<button class="button button-small" name="action" value="{{ ['paid' => 'process', 'processing' => 'ship', 'shipped' => 'deliver'][$order->status] }}">{{ ['paid' => 'Iniciar separação', 'processing' => 'Enviar pedido', 'shipped' => 'Confirmar entrega'][$order->status] }}</button></form>
        @endif
        @if($order->canBeCancelled() && $order->paid_at)
            <details><summary>Exercício QS-19: falha de estorno</summary><p>Uma falha simulada deve preservar o estado do pedido e o estoque. Depois, tente o cancelamento normal.</p><form method="POST" action="{{ route('orders.cancellation.store', $order) }}">@csrf<input type="hidden" name="simulate_refund_failure" value="1"><button class="button button-secondary button-small">Tentar cancelar com falha de estorno</button></form></details>
        @endif
    </section>
    <section class="panel form-panel lifecycle-panel"><p class="section-label">Rastreabilidade</p><h2>Histórico do pedido</h2><ol class="event-list">@forelse($order->events as $event)<li><strong>{{ $event->description }}</strong><small>{{ $event->created_at->format('d/m/Y H:i:s') }} · {{ $event->user?->name ?? 'Sistema' }} @if($event->amount !== null) · R$ {{ number_format((float) $event->amount, 2, ',', '.') }} @endif</small></li>@empty<li>Pedido anterior à implantação do histórico.</li>@endforelse</ol></section>
    <a class="text-link back-link" href="{{ route('orders.index') }}">← Voltar aos pedidos</a>
@endsection
