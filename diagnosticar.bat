@echo off
setlocal EnableExtensions EnableDelayedExpansion
chcp 65001 > nul
cd /d "%~dp0"
set "FALHAS=0"

echo.
echo ========================================
echo  DIAGNOSTICO DO QUALITYSHOP
echo ========================================

where git > nul 2>&1
if errorlevel 1 (echo [FALHA] Git nao encontrado.& set /a FALHAS+=1) else (for /f "delims=" %%V in ('git --version') do echo [OK] %%V)

where php > nul 2>&1
if errorlevel 1 (
    echo [FALHA] PHP nao encontrado.
    set /a FALHAS+=1
) else (
    for /f "delims=" %%V in ('php -r "echo PHP_VERSION;"') do set "PHP_VERSION=%%V"
    php -r "exit(PHP_VERSION_ID >= 80300 ? 0 : 1);"
    if errorlevel 1 (echo [FALHA] PHP !PHP_VERSION!; esperado 8.3 ou superior.& set /a FALHAS+=1) else (echo [OK] PHP !PHP_VERSION!)
)

where composer > nul 2>&1
if errorlevel 1 (echo [FALHA] Composer nao encontrado.& set /a FALHAS+=1) else (for /f "delims=" %%V in ('composer --version 2^>nul') do echo [OK] %%V)

php -r "exit(extension_loaded('pdo_sqlite') ? 0 : 1);" > nul 2>&1
if errorlevel 1 (echo [FALHA] Extensao pdo_sqlite ausente.& set /a FALHAS+=1) else (echo [OK] SQLite habilitado.)

if exist ".env" (echo [OK] Arquivo .env encontrado.) else (echo [FALHA] Arquivo .env ausente.& set /a FALHAS+=1)
if exist "vendor\autoload.php" (echo [OK] Dependencias instaladas.) else (echo [FALHA] Pasta vendor ausente.& set /a FALHAS+=1)
if exist "database\database.sqlite" (echo [OK] Banco database.sqlite encontrado.) else (echo [FALHA] Banco SQLite ausente.& set /a FALHAS+=1)

set "TESTE_ESCRITA=storage\.qualityshop-write-test-!RANDOM!.tmp"
> "!TESTE_ESCRITA!" echo ok
if exist "!TESTE_ESCRITA!" (
    del /q "!TESTE_ESCRITA!"
    echo [OK] Pasta storage com permissao de escrita.
) else (
    echo [FALHA] Pasta storage sem permissao de escrita.
    set /a FALHAS+=1
)

if exist "vendor\autoload.php" if exist ".env" (
    php artisan migrate:status --no-ansi > nul 2>&1
    if errorlevel 1 (echo [FALHA] Laravel nao conseguiu consultar as migracoes.& set /a FALHAS+=1) else (echo [OK] Laravel conectado ao banco.)
)

netstat -ano | findstr /R /C:":8000 .*LISTENING" > nul
if errorlevel 1 (echo [INFO] Porta 8000 livre.) else (echo [INFO] Porta 8000 ja esta em uso.)

echo.
if !FALHAS! EQU 0 (
    echo [RESULTADO] Ambiente saudavel e pronto para os testes.
    exit /b 0
)

echo [RESULTADO] Foram encontradas !FALHAS! falha(s). Corrija os itens acima e execute novamente.
exit /b 1
