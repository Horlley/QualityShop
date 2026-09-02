# QualityShop

O QualityShop é o laboratório oficial do livro **QA Moderno — Fundamentos e Prática**. Ele simula um comércio eletrônico e evoluirá junto com os capítulos, permitindo que o leitor pratique testes, investigação, APIs, banco de dados, automação e segurança em um ambiente próprio.

## Filosofia desta primeira edição

- Ambiente totalmente local e gratuito.
- Laravel com servidor próprio de desenvolvimento.
- SQLite para evitar a instalação de um servidor de banco de dados.
- Sem Docker, Apache, Nginx, MySQL, PostgreSQL ou serviços em nuvem.
- Sem necessidade de Node.js, NPM ou Bun para executar o laboratório.
- Dados fictícios e restauráveis para permitir experimentos seguros.

## Requisitos

- Git.
- PHP 8.3 ou superior com `pdo_sqlite` habilitado.
- Composer 2.
- Navegador moderno.


### Versões validadas nesta edição

| Componente | Versão validada |
| --- | --- |
| Laravel | 13.30.1 |
| PHP | 8.5.10 — mínimo aceito: 8.3 |
| Composer | 2.9.2 |
| SQLite | 3.53.4 |

Confirme os requisitos no PowerShell:

```powershell
git --version
php --version
composer --version
php -r "echo extension_loaded('pdo_sqlite') ? 'SQLite OK' : 'SQLite AUSENTE';"
```

## Primeira configuração no Windows

```powershell
git clone https://github.com/Horlley/QualityShop.git
cd QualityShop
.\configurar.bat
.\iniciar.bat
```

Abra <http://127.0.0.1:8000>. A mensagem **Ambiente local pronto para os testes** confirma a primeira evidência do laboratório.

## Comandos do laboratório

| Comando | Finalidade |
| --- | --- |
| `configurar.bat` | Instala dependências, cria o `.env`, prepara o SQLite e executa as migrações. |
| `iniciar.bat` | Inicia o servidor local na porta 8000. |
| `restaurar.bat` | Apaga os dados locais e recria o estado inicial do laboratório. |
| `diagnosticar.bat` | Verifica versões, SQLite, arquivos, permissões, banco e porta. |

O arquivo `.env` contém a configuração individual do ambiente e não deve ser enviado ao Git.

## Estado atual

Esta etapa entrega a fundação técnica do laboratório: Laravel 13, SQLite, página inicial própria, rota de saúde em `/up`, scripts de apoio e testes automatizados básicos. As funcionalidades de login, dashboard, projetos, equipe e tarefas serão adicionadas em etapas didáticas.

## Testes do projeto

```powershell
php artisan test --compact
```

O QualityShop é um ambiente didático autorizado. Execute exercícios de segurança apenas nesta instalação local.
