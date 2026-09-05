@extends('layouts.app')

@section('title', 'Início')

@section('content')
    <section class="hero hero-grid" aria-labelledby="page-title">
        <div>
            <div class="eyebrow">Laboratório oficial do livro QA Moderno</div>
            <h1 id="page-title">Aprenda QA em um produto que você pode <span>investigar.</span></h1>
            <p class="hero-copy">Um comércio eletrônico completo, local e seguro para praticar requisitos, testes, APIs, banco de dados, automação, segurança e comunicação profissional.</p>
            <div class="button-row">
                <a class="button" href="{{ route('catalog.index') }}">Explorar o catálogo</a>
                @guest
                    <a class="button button-secondary" href="{{ route('login') }}">Entrar no laboratório</a>
                @else
                    <a class="button button-secondary" href="{{ route('dashboard') }}">Abrir meu painel</a>
                @endguest
            </div>
            <div class="ready" role="status"><span class="ready-dot" aria-hidden="true"></span> Ambiente local pronto para os testes</div>
        </div>
        <aside class="mission-card" aria-label="Missão em destaque">
            <span class="mission-code">MISSÃO QS-21</span>
            <h2>Do requisito à decisão de release</h2>
            <p>Planeje, execute, investigue e apresente evidências usando a mesma aplicação durante toda a jornada.</p>
            <dl>
                <div><dt>Produtos ativos</dt><dd>{{ $activeProducts }}</dd></div>
                <div><dt>Pedidos registrados</dt><dd>{{ $completedOrders }}</dd></div>
                <div><dt>Infraestrutura</dt><dd>SQLite</dd></div>
            </dl>
        </aside>
    </section>

    <section class="section-block" aria-labelledby="journey-title">
        <div class="section-heading">
            <div><p class="section-label">Fluxo principal</p><h2 id="journey-title">Uma compra, muitas perguntas de qualidade</h2></div>
            <p>Observe a interface e depois confirme a história na API, no banco e nos testes.</p>
        </div>
        <div class="flow-grid">
            @foreach([['01','Login','identidade e perfis'],['02','Catálogo','busca e estoque'],['03','Carrinho','limites e cupom'],['04','Checkout','valor e pagamento'],['05','Pedidos','estado e autorização'],['06','Cancelamento','regressão e evidência']] as [$number,$title,$copy])
                <article><span>{{ $number }}</span><h3>{{ $title }}</h3><p>{{ $copy }}</p></article>
            @endforeach
        </div>
    </section>

    <section class="lab-banner">
        <div><p class="section-label">Feito para iniciantes</p><h2>Sem Docker. Sem servidor pago. Sem distrações.</h2></div>
        <p>Laravel inicia o servidor local, o SQLite guarda os dados e os scripts do projeto restauram o laboratório quando você quiser recomeçar.</p>
    </section>
@endsection
