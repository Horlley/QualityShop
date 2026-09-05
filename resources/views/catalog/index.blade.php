@extends('layouts.app')

@section('title', 'Catálogo')

@section('content')
    <header class="page-heading">
        <div><p class="section-label">Catálogo</p><h1>Produtos para testar, não apenas comprar.</h1></div>
        <p>Use filtros, compare estados de estoque e observe como as mensagens mudam conforme os dados.</p>
    </header>

    <form class="filter-bar" method="GET" action="{{ route('catalog.index') }}">
        <div><label for="search">Nome ou SKU</label><input id="search" name="search" value="{{ $search }}" placeholder="Ex.: Aurora ou QS-001"></div>
        <div><label for="category">Categoria</label><select id="category" name="category"><option value="">Todas</option>@foreach($categories as $item)<option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>@endforeach</select></div>
        <button class="button button-small" type="submit">Filtrar</button>
        @if($search !== '' || $category !== '')<a class="text-link" href="{{ route('catalog.index') }}">Limpar</a>@endif
    </form>

    <div class="product-grid">
        @forelse($products as $product)
            <article class="product-card">
                <div class="product-visual" aria-hidden="true"><span>{{ str($product->name)->substr(0, 1) }}</span><small>{{ $product->sku }}</small></div>
                <div class="product-body">
                    <span class="pill">{{ $product->category }}</span>
                    <h2>{{ $product->name }}</h2>
                    <p>{{ $product->description }}</p>
                    <div class="product-meta"><strong>R$ {{ number_format((float) $product->price, 2, ',', '.') }}</strong><span @class(['stock-low' => $product->stock <= 5])>{{ $product->stock > 0 ? $product->stock.' em estoque' : 'Sem estoque' }}</span></div>
                    @auth
                        <form class="add-form" method="POST" action="{{ route('cart.items.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <label for="quantity-{{ $product->id }}">Quantidade</label>
                            <input id="quantity-{{ $product->id }}" name="quantity" type="number" value="1" min="1" max="10" @disabled($product->stock === 0)>
                            <button class="button button-small" type="submit" @disabled($product->stock === 0)>{{ $product->stock > 0 ? 'Adicionar' : 'Indisponível' }}</button>
                        </form>
                    @else
                        <a class="button button-small button-full" href="{{ route('login') }}">Entrar para comprar</a>
                    @endauth
                </div>
            </article>
        @empty
            <div class="empty-state"><h2>Nenhum produto encontrado</h2><p>Altere os filtros e tente novamente.</p></div>
        @endforelse
    </div>
@endsection
