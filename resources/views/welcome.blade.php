<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name') }} | Laboratório de QA</title>

        <link rel="stylesheet" href="{{ asset('css/qualityshop.css') }}">
    </head>
    <body>
        <main class="shell">
            <section class="hero" aria-labelledby="page-title">
                <div class="eyebrow">Laboratório oficial do livro QA Moderno</div>
                <h1 id="page-title">Quality<span>Shop</span></h1>
                <p class="hero-copy">
                    Um comércio eletrônico criado para você aprender a observar,
                    testar, investigar e comunicar qualidade como um profissional.
                </p>

                <div class="ready" role="status">
                    <span class="ready-dot" aria-hidden="true"></span>
                    Ambiente local pronto para os testes
                </div>
            </section>

            <section class="status-grid" aria-label="Estado do laboratório">
                <article class="status-card">
                    <span class="card-number">01</span>
                    <h2>Laravel</h2>
                    <p>Aplicação instalada e respondendo no servidor local.</p>
                </article>

                <article class="status-card">
                    <span class="card-number">02</span>
                    <h2>SQLite</h2>
                    <p>Banco leve, isolado e preparado para restaurações rápidas.</p>
                </article>

                <article class="status-card">
                    <span class="card-number">03</span>
                    <h2>Sua missão</h2>
                    <p>Registrar esta tela como a primeira evidência do ambiente.</p>
                </article>
            </section>

            <section class="journey" aria-labelledby="journey-title">
                <div>
                    <p class="section-label">O que vem a seguir</p>
                    <h2 id="journey-title">Uma aplicação que evolui junto com o leitor</h2>
                </div>

                <ol>
                    <li><strong>Login</strong><span>acesso e perfis</span></li>
                    <li><strong>Dashboard</strong><span>visão do trabalho</span></li>
                    <li><strong>Projetos</strong><span>equipe, etapas e tarefas</span></li>
                    <li><strong>Qualidade</strong><span>riscos, testes e evidências</span></li>
                </ol>
            </section>
        </main>

        <footer>
            <span>QualityShop</span>
            <span>Ambiente didático local</span>
        </footer>
    </body>
</html>
