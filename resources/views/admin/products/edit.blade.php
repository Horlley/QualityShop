@extends('layouts.app')
@section('title', 'Editar '.$product->name)
@section('content')
    <header class="page-heading compact"><div><p class="section-label">Administração</p><h1>Editar {{ $product->name }}</h1></div><code>{{ $product->sku }}</code></header>
    <form class="panel form-panel wide" method="POST" action="{{ route('admin.products.update', $product) }}">@csrf @method('PUT') @include('admin.products._form')</form>
@endsection
