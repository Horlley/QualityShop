# QualityShop

O QualityShop é o laboratório oficial do livro **QA Moderno — Fundamentos e Prática**. Ele simula um comércio eletrônico completo para o leitor praticar testes, investigação, APIs, banco de dados, automação e segurança em um ambiente próprio.

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

## Contas didáticas

Todas as contas usam a senha local `Quality123!`.

| Perfil | E-mail | Experiência disponível |
| --- | --- | --- |
| Cliente | `cliente@qualityshop.local` | Catálogo, carrinho, checkout e próprios pedidos |
| Operador | `operador@qualityshop.local` | Acompanhamento de todos os pedidos |
| Gerente | `gerente@qualityshop.local` | Indicadores e visão da operação |
| Administrador | `admin@qualityshop.local` | Administração de produtos e estoque |
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

## Testes do projeto

```powershell
php artisan test --compact
```

## Integração contínua

O arquivo `.github/workflows/tests.yml` repete no GitHub a mesma verificação usada localmente. A cada envio para a branch `main`, pull request ou execução manual, o fluxo prepara PHP 8.5 com SQLite, instala as dependências, audita os pacotes do Composer e executa a suíte.

Um resultado verde significa que as verificações programadas passaram naquele commit. Ele não substitui exploração, análise de risco nem revisão humana.

O QualityShop é um ambiente didático autorizado. Execute exercícios de segurança apenas nesta instalação local.
