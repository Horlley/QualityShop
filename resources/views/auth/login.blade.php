@extends('layouts.app')

@section('title', 'Entrar')

@section('content')
    <section class="auth-grid">
        <div class="auth-copy">
            <p class="section-label">Acesso didático</p>
            <h1>Entre como cada perfil e observe o que muda.</h1>
            <p>As contas são fictícias e existem somente no seu SQLite local. Use a mesma senha em todos os perfis ativos.</p>
            <div class="credential-list">
                @foreach([
                    ['Cliente','cliente@qualityshop.local','Compra e acompanha os próprios pedidos'],
                    ['Cliente 2','cliente2@qualityshop.local','Permite testar isolamento entre clientes'],
                    ['Auditoria','auditor@qualityshop.local','Consulta pedidos e histórico sem alterá-los'],
                    ['Operador','operador@qualityshop.local','Acompanha pedidos dos clientes'],
                    ['Gerente','gerente@qualityshop.local','Visualiza indicadores da operação'],
                    ['Administrador','admin@qualityshop.local','Gerencia produtos, estoque e contas'],
                    ['Usuário bloqueado','bloqueado@qualityshop.local','Deve receber uma recusa segura'],
                ] as [$role,$email,$description])
                    <article><strong>{{ $role }}</strong><code>{{ $email }}</code><span>{{ $description }}</span></article>
                @endforeach
            </div>
            <p class="password-hint">Senha didática: <code>Quality123!</code></p>
        </div>
        <form class="panel form-panel" method="POST" action="{{ route('login.store') }}">
            @csrf
            <p class="section-label">Sessão local</p>
            <h2>Entrar no QualityShop</h2>
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email', 'cliente@qualityshop.local') }}" autocomplete="username" required autofocus>
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" value="Quality123!" autocomplete="current-password" required>
            <button class="button button-full" type="submit">Entrar no laboratório</button>
            <p class="form-note">Experimente depois uma senha incorreta e o usuário bloqueado. Compare a mensagem apresentada nos dois casos.</p>
        </form>
    </section>
@endsection
