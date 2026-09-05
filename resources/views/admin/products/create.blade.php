@extends('layouts.app')
@section('title', 'Novo produto')
@section('content')
    <header class="page-heading compact"><div><p class="section-label">Administração</p><h1>Novo produto</h1></div></header>
    <form class="panel form-panel wide" method="POST" action="{{ route('admin.products.store') }}">@csrf @include('admin.products._form')</form>
@endsection
