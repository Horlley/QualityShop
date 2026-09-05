@extends('layouts.app')

@section('title', 'Painel')

@section('content')
    <header class="page-heading">
        <div><p class="section-label">Painel {{ auth()->user()->roleLabel() }}</p><h1>Olá, {{ str(auth()->user()->name)->before(' ') }}. O que merece atenção?</h1></div>
        <p>Cada perfil observa o mesmo produto por uma perspectiva diferente. Compare acesso, indicadores e decisões.</p>
    </header>

    <div class="metric-grid">
        <article><span>Produtos ativos</span><strong>{{ $activeProducts }}</strong><small>oferta disponível</small></article>
        <article><span>{{ auth()->user()->isStaff() ? 'Pedidos no sistema' : 'Meus pedidos' }}</span><strong>{{ $ordersCount }}</strong><small>registros visíveis</small></article>
        <article><span>Estoque baixo</span><strong>{{ $lowStockProducts }}</strong><small>cinco unidades ou menos</small></article>
        <article><span>Valor aprovado</span><strong>R$ {{ number_format($salesTotal, 2, ',', '.') }}</strong><small>sem pedidos cancelados</small></article>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-heading"><div><p class="section-label">Atividade recente</p><h2>Pedidos</h2></div><a class="text-link" href="{{ route('orders.index') }}">Ver todos</a></div>
            @forelse($recentOrders as $order)
                <a class="order-row" href="{{ route('orders.show', $order) }}"><span><strong>{{ $order->number }}</strong><small>{{ $order->user->name }} · {{ $order->created_at->format('d/m/Y H:i') }}</small></span><span><b class="status status-{{ $order->status }}">{{ $order->statusLabel() }}</b><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></span></a>
            @empty
                <div class="empty-inline">Nenhum pedido registrado. O catálogo é um bom ponto de partida.</div>
            @endforelse
        </section>
        <aside class="panel mission-list">
            <p class="section-label">Próximas verificações</p><h2>Missões sugeridas</h2>
            <ol><li><span>01</span>Compare login válido, inválido e bloqueado.</li><li><span>02</span>Teste quantidade 0, 1, 10 e 11 no carrinho.</li><li><span>03</span>Aplique QA10 abaixo e acima de R$ 100.</li><li><span>04</span>Tente abrir o pedido de outro cliente.</li></ol>
            @if(auth()->user()->role === 'admin')<a class="button button-secondary button-full" href="{{ route('admin.products.index') }}">Gerenciar catálogo</a>@endif
        </aside>
    </div>
@endsection
