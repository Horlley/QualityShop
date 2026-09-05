<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'QualityShop') | Laboratório de QA</title>
    <link rel="stylesheet" href="{{ asset('css/qualityshop.css') }}">
</head>
<body>
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <header class="site-header">
        <div class="nav-shell">
            <a class="brand" href="{{ route('home') }}" aria-label="QualityShop — início">Quality<span>Shop</span></a>
            <nav class="main-nav" aria-label="Navegação principal">
                <a @class(['active' => request()->routeIs('home')]) href="{{ route('home') }}">Início</a>
                <a @class(['active' => request()->routeIs('catalog.*')]) href="{{ route('catalog.index') }}">Catálogo</a>
                <a @class(['active' => request()->routeIs('lab.*')]) href="{{ route('lab.index') }}">Guia QA</a>
                @auth
                    <a @class(['active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}">Painel</a>
                    <a @class(['active' => request()->routeIs('orders.*')]) href="{{ route('orders.index') }}">Pedidos</a>
                    @if(in_array(auth()->user()->role, ['operator', 'admin']))
                        <a href="{{ route('users.index') }}">Contas</a>
                    @endif
                    @if(in_array(auth()->user()->role, ['manager', 'admin']))
                        <a href="{{ route('stock.index') }}">Estoque</a>
                    @endif
                    @if(auth()->user()->role === 'admin')
                        <a @class(['active' => request()->routeIs('admin.*')]) href="{{ route('admin.products.index') }}">Administração</a>
                    @endif
                @endauth
            </nav>
            <div class="nav-actions">
                @auth
                    <a class="cart-link" href="{{ route('cart.index') }}">Carrinho <span>{{ array_sum(session('cart', [])) }}</span></a>
                    <div class="account-chip">
                        <small>{{ auth()->user()->roleLabel() }}</small>
                        <strong>{{ auth()->user()->name }}</strong>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="link-button" type="submit">Sair</button>
                    </form>
                @else
                    <a class="button button-small" href="{{ route('login') }}">Entrar</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="conteudo" class="page-shell">
        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" role="alert">
                <strong>Revise antes de continuar:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div>
            <strong>QualityShop</strong>
            <span>Laboratório oficial do livro QA Moderno</span>
        </div>
        <div class="footer-status"><i aria-hidden="true"></i> Local · SQLite · Dados fictícios</div>
    </footer>
</body>
</html>
