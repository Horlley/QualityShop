@extends('layouts.app')
@section('title', $managedUser->exists ? 'Editar conta' : 'Nova conta')
@section('content')
<header class="page-heading compact"><div><p class="section-label">Gestão de acesso</p><h1>{{ $managedUser->exists ? 'Editar conta' : 'Nova conta' }}</h1></div></header>
<form class="panel form-panel wide" method="POST" action="{{ $managedUser->exists ? route('users.update', $managedUser) : route('users.store') }}">
@csrf @if($managedUser->exists) @method('PUT') @endif
<div class="form-grid"><div><label for="name">Nome fictício</label><input id="name" name="name" required maxlength="120" value="{{ old('name', $managedUser->name) }}"></div><div><label for="email">E-mail fictício</label><input id="email" name="email" type="email" required maxlength="200" value="{{ old('email', $managedUser->email) }}"></div>
<div><label for="role">Perfil</label><select id="role" name="role">@foreach(['customer' => 'Cliente', 'operator' => 'Operador', 'manager' => 'Gerente', 'admin' => 'Administrador', 'auditor' => 'Auditoria'] as $value => $label)@if(auth()->user()->role === 'admin' || $value === 'customer')<option value="{{ $value }}" @selected(old('role', $managedUser->role) === $value)>{{ $label }}</option>@endif @endforeach</select></div>
<div><label for="active">Estado</label><select id="active" name="active"><option value="1" @selected(old('active', $managedUser->active))>Ativo</option><option value="0" @selected(! old('active', $managedUser->active))>Inativo</option></select></div>
<div><label for="password">Senha (mínimo 8 caracteres)</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" @required(! $managedUser->exists)><small>Ao editar, deixe vazia para preservar.</small></div><div><label for="password_confirmation">Confirmar senha</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div></div>
<button class="button" type="submit">Salvar conta</button> <a class="text-link" href="{{ route('users.index') }}">Voltar</a>
</form>
@endsection
