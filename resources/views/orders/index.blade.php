@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')
    <header class="page-heading compact">
        <div><p class="section-label">Pedidos</p><h1>{{ auth()->user()->isStaff() ? 'Visão da operação' : 'Seu histórico de compras' }}</h1></div>
        <a class="button button-secondary button-small" href="{{ route('catalog.index') }}">Nova compra</a>
    </header>
    <section class="panel table-panel">
        @if($orders->isEmpty())
            <div class="empty-state"><h2>Nenhum pedido registrado</h2><p>Conclua uma compra para acompanhar a transição de estados.</p></div>
        @else
            <div class="table-scroll"><table><thead><tr><th>Pedido</th>@if(auth()->user()->isStaff())<th>Cliente</th>@endif<th>Estado</th><th>Itens</th><th>Total</th><th></th></tr></thead><tbody>
            @foreach($orders as $order)
                <tr><td><strong>{{ $order->number }}</strong><small>{{ $order->created_at->format('d/m/Y H:i') }}</small></td>@if(auth()->user()->isStaff())<td>{{ $order->user->name }}</td>@endif<td><b class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</b></td><td>{{ $order->items->sum('quantity') }}</td><td><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></td><td><a class="text-link" href="{{ route('orders.show', $order) }}">Detalhes</a></td></tr>
            @endforeach
            </tbody></table></div>
        @endif
    </section>
@endsection
