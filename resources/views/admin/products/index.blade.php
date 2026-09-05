@extends('layouts.app')

@section('title', 'Administração de produtos')

@section('content')
    <header class="page-heading compact"><div><p class="section-label">Administração</p><h1>Produtos, estoque e disponibilidade.</h1></div><a class="button button-small" href="{{ route('admin.products.create') }}">Novo produto</a></header>
    <section class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>SKU</th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Estado</th><th></th></tr></thead><tbody>@foreach($products as $product)<tr><td><code>{{ $product->sku }}</code></td><td><strong>{{ $product->name }}</strong></td><td>{{ $product->category }}</td><td>R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td><td>{{ $product->stock }}</td><td><b class="status {{ $product->active ? 'status-paid' : 'status-cancelled' }}">{{ $product->active ? 'Ativo' : 'Inativo' }}</b></td><td><a class="text-link" href="{{ route('admin.products.edit', $product) }}">Editar</a></td></tr>@endforeach</tbody></table></div></section>
@endsection
