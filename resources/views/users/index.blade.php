@extends('layouts.app')
@section('title', 'Contas e clientes')
@section('content')
<header class="page-heading compact"><div><p class="section-label">Pessoas · dados fictícios</p><h1>Contas e clientes.</h1></div><a class="button button-small" href="{{ route('users.create') }}">Nova conta</a></header>
<form class="filter-bar" method="GET"><div><label for="search">Pesquisar por nome</label><input id="search" name="search" value="{{ request('search') }}"></div><button class="button" type="submit">Pesquisar</button></form>
<section class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Estado</th><th>Ação</th></tr></thead><tbody>
@forelse($users as $person)<tr><td>{{ $person->name }}</td><td>{{ $person->email }}</td><td>{{ $person->roleLabel() }}</td><td>{{ $person->active ? 'Ativo' : 'Inativo' }}</td><td>@if(auth()->user()->role === 'admin')<a href="{{ route('users.edit', $person) }}">Editar</a>@else Consulta @endif</td></tr>@empty<tr><td colspan="5">Nenhuma conta encontrada.</td></tr>@endforelse
</tbody></table></div></section>
<nav class="action-row" aria-label="Paginação">@if($users->previousPageUrl())<a href="{{ $users->previousPageUrl() }}">← Anterior</a>@endif<span>Página {{ $users->currentPage() }} de {{ $users->lastPage() }}</span>@if($users->nextPageUrl())<a href="{{ $users->nextPageUrl() }}">Próxima →</a>@endif</nav>
@endsection
