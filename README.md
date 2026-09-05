# QualityShop

O QualityShop é o laboratório oficial do livro **QA Moderno — Fundamentos e Prática**. Ele entrega a loja simulada da primeira edição para praticar testes, investigação, APIs, banco de dados, automação e segurança em um ambiente próprio. Não é uma loja de produção.

## Filosofia desta primeira edição

- Ambiente totalmente local e gratuito.
- Laravel com servidor próprio de desenvolvimento.
- SQLite para evitar a instalação de um servidor de banco de dados.
- Sem Docker, Apache, Nginx, MySQL, PostgreSQL ou serviços em nuvem.
- Sem necessidade de Node.js, NPM ou Bun para executar o laboratório.
- Dados fictícios e restauráveis para permitir experimentos seguros.

## Requisitos

- Git.
- PHP 8.5 recomendado; mínimo efetivo das dependências: 8.4.1. Habilite `pdo_sqlite`.
- Composer 2.
- Navegador moderno.


### Versões validadas nesta edição

| Componente | Versão validada |
| --- | --- |
| Laravel | 13.30.1 |
| PHP | 8.5.10 — mínimo efetivo: 8.4.1 |
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
| `testar.bat` | Executa PHPUnit usando o PHP compatível, em SQLite separado em memória. |

O arquivo `.env` contém a configuração individual do ambiente e não deve ser enviado ao Git.

Se o terminal encontrar outro PHP, aponte o executável compatível antes de usar os scripts:

```powershell
$env:QUALITYSHOP_PHP = 'C:\php85\php.exe'
.\diagnosticar.bat
```

Substitua o caminho pelo local onde você instalou o PHP. `ambiente.bat` altera apenas o processo atual; não modifica o Windows. Na cópia de desenvolvimento do autor ele também reconhece o runtime na pasta irmã `.runtime_php85`; esse runtime não acompanha o Git e não é necessário para quem já tem PHP compatível instalado.

Configurar preserva o `.env` e os dados existentes. Antes de migrar ou restaurar, o script copia o banco para `storage/app/private/backups`. Restaurar recria o banco ativo após confirmação. Para recuperar uma cópia, pare o servidor, preserve o banco atual em outro arquivo e copie o backup escolhido para `database/database.sqlite`. Não faça isso com o servidor em execução.

## Contas didáticas

Todas as contas usam a senha local `Quality123!`.

| Perfil | E-mail | Experiência disponível |
| --- | --- | --- |
| Cliente | `cliente@qualityshop.local` | Catálogo, carrinho, checkout e próprios pedidos |
| Cliente 2 | `cliente2@qualityshop.local` | Segunda identidade para teste de isolamento |
| Operador | `operador@qualityshop.local` | Cadastro de clientes, compras em nome deles e operação de pedidos |
| Gerente | `gerente@qualityshop.local` | Indicadores, pedidos e ajuste de estoque |
| Administrador | `admin@qualityshop.local` | Produtos, estoque, contas, perfis e bloqueio |
| Auditoria | `auditor@qualityshop.local` | Pedidos e histórico, sem alteração pela interface |
| Bloqueado | `bloqueado@qualityshop.local` | Cenário negativo de autenticação |

Essas credenciais são públicas porque pertencem somente ao banco fictício criado na máquina do leitor. Não reutilize a senha em contas reais.

## Fluxos implementados

- login com perfis e usuário bloqueado;
- catálogo com busca, categoria, disponibilidade e estoque;
- carrinho com quantidade mínima 1 e máxima 10;
- cupom `QA10`, válido para subtotal a partir de R$ 100,00;
- checkout e pagamento simulados, sem coleta de dados financeiros;
- criação de pedido, itens e atualização atômica de estoque;
- histórico com isolamento dos pedidos de cada cliente;
- cancelamento QS-19 com controle de estado e devolução do estoque;
- painel adaptado aos perfis cliente, operador, gerente e administrador;
- administração de produtos protegida por perfil;
- cenário de defeito controlado QS-BUG-01, ativado conscientemente no carrinho;
- API de produtos, testes PHPUnit e execução contínua no GitHub Actions.

### Regras da compra e da QS-19

- Frete gratuito; desconto permitido é `QA10` (10%, subtotal mínimo R$ 100,00, sem acumular). `QAEXPIRADO` e outros códigos são recusados.
- O estoque é reservado ao criar o pedido, inclusive quando o pagamento fica pendente. Não há expiração automática: cancele pedidos pendentes ao concluir os exercícios.
- Pagamento simulado pode aprovar, ficar pendente, recusar ou retornar timeout. Não há cartão, Pix real ou comunicação com provedores externos.
- Estados: aguardando pagamento → pago → em separação → enviado → entregue. Cancelamento só antes do envio.
- Pagamento aprovado é estornado ao cancelar; uma falha de estorno simulada preserva pedido e estoque. A devolução de estoque não se repete.
- A chave de checkout é persistida com unicidade: reenviar a mesma compra não cria outro pedido. Reaprovar pagamento não gera outro evento de cobrança.
- Valores são recalculados pelo servidor, com arredondamento em centavos. SQLite usa transações IMMEDIATE e verificação de saldo para proteger a operação local.
- Histórico de pedido aparece na tela e em `order_events`; alterações de contas e ajustes de estoque ficam em `storage/logs/laravel.log` (não inclua arquivos de log brutos no portfólio sem revisar).

Dados de fronteira: QS-060 custa R$ 60,00 e começa com estoque 10; QS-040 custa R$ 40,00; QS-099 custa R$ 99,99. QS-003 está sem estoque e QS-004 inativo. Consulte o menu **Guia QA** para as missões por capítulo.

### Cenário controlado

O carrinho apresenta um painel para ativar o `QS-BUG-01`. Quando ativo, a quantidade pode mudar sem que o total visual seja recalculado. O comportamento é deliberado, identificado na tela, limitado à sessão e desativado automaticamente após o checkout. Use-o para praticar descoberta, evidência, relatório, reteste e regressão.

### API de produtos

O endereço-base é `http://127.0.0.1:8000/api/v1`.

| Método | Rota | Finalidade |
| --- | --- | --- |
| `GET` | `/products` | Lista os produtos. Aceita `category` e `active` como filtros. |
| `GET` | `/products/{id}` | Consulta um produto. |
| `POST` | `/products` | Cadastra um produto. |
| `PATCH` | `/products/{id}` | Atualiza somente os campos enviados. |
| `DELETE` | `/products/{id}` | Exclui um produto. |

Exemplo de consulta no navegador ou no Postman:

```text
http://127.0.0.1:8000/api/v1/products?active=1
```

A API é pública apenas porque foi criada para o laboratório local, conforme explicado nos capítulos de API e segurança. Ela não deve ser publicada na internet nesse estado.

Esta exceção didática permite CRUD sem autenticação na API, independentemente do perfil usado na interface. Não use a API pública como prova de autorização dos perfis web. O middleware recusa origens não loopback e ambientes diferentes de local/testing; isso é contenção do laboratório, não garantia de segurança para produção.

Materiais: `public/material/QualityShop.postman_collection.json` e `public/material/consultas-qa.sql`, também disponíveis no Guia QA. A coleção deve ser executada em ordem; ela cria um produto próprio e o exclui ao final. Leia cada operação antes de executar.

Limites desta edição: sem gateway real, webhooks, transportadora, e-mail, nota fiscal ou implantação pública. Os casos conceituais do livro sobre integrações servem para refinamento e análise de risco, não afirmam que esses serviços existem no laboratório.

## Testes do projeto

```powershell
.\testar.bat
```

## Integração contínua

O arquivo `.github/workflows/tests.yml` repete no GitHub a mesma verificação usada localmente. A cada envio para a branch `main`, pull request ou execução manual, o fluxo prepara PHP 8.5 com SQLite, instala as dependências, audita os pacotes do Composer e executa a suíte.

Um resultado verde significa que as verificações programadas passaram naquele commit. Ele não substitui exploração, análise de risco nem revisão humana.

O workflow só roda remotamente depois que o código é enviado ao GitHub. Os arquivos presentes localmente não são evidência de uma execução remota.

O QualityShop é um ambiente didático autorizado. Execute exercícios de segurança apenas nesta instalação local.
