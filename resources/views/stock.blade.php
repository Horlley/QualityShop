@extends('layouts.app')
@section('title', 'Estoque')
@section('content')
<header class="page-heading compact"><div><p class="section-label">Operação</p><h1>Disponibilidade em números.</h1><p>Saldo disponível para novas compras. Pedidos pendentes já reservaram seus itens.</p></div></header>
<section class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>SKU</th><th>Produto</th><th>Disponível</th><th>Ajuste de saldo</th></tr></thead><tbody>@foreach($products as $product)<tr><td><code>{{ $product->sku }}</code></td><td>{{ $product->name }}<small>{{ $product->active ? 'Ativo' : 'Inativo' }}</small></td><td>{{ $product->stock }}</td><td><form method="POST" class="quantity-form" action="{{ route('stock.update', $product) }}">@csrf @method('PATCH')<input type="hidden" name="previous_stock" value="{{ $product->stock }}"><input aria-label="Novo saldo de {{ $product->name }}" type="number" min="0" max="100000" name="stock" value="{{ $product->stock }}" required><button class="icon-button">Salvar saldo</button></form></td></tr>@endforeach</tbody></table></div></section>
@endsection
