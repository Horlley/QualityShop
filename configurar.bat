@echo off
setlocal EnableExtensions EnableDelayedExpansion
chcp 65001 > nul
cd /d "%~dp0"

echo.
echo [QualityShop] Preparando o ambiente local...
call "%~dp0ambiente.bat"
if errorlevel 1 exit /b 1

where php > nul 2>&1
if errorlevel 1 (
    echo [ERRO] PHP nao foi encontrado. Instale o PHP 8.5.
    exit /b 1
)

php -r "exit(PHP_VERSION_ID >= 80401 ? 0 : 1);"
if errorlevel 1 (
    for /f "delims=" %%V in ('php -r "echo PHP_VERSION;"') do set "PHP_VERSION=%%V"
    echo [ERRO] PHP !PHP_VERSION! encontrado. O QualityShop exige PHP 8.4.1 ou superior.
    exit /b 1
)

where composer > nul 2>&1
if errorlevel 1 (
    echo [ERRO] Composer nao foi encontrado.
    exit /b 1
)

php -r "exit(extension_loaded('pdo_sqlite') ? 0 : 1);"
if errorlevel 1 (
    echo [ERRO] A extensao pdo_sqlite nao esta habilitada no PHP.
    exit /b 1
)

if not exist ".env" (
    copy ".env.example" ".env" > nul
    echo [OK] Arquivo .env criado.
) else (
    echo [OK] Arquivo .env preservado.
)

if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
    echo [OK] Banco SQLite criado.
) else (
    echo [OK] Banco SQLite preservado.
)

echo [1/4] Instalando dependencias PHP...
call composer install --no-interaction --prefer-dist
if errorlevel 1 exit /b 1

set "APP_KEY_VALUE="
for /f "tokens=1,* delims==" %%A in ('findstr /B /C:"APP_KEY=" ".env"') do set "APP_KEY_VALUE=%%B"
if not defined APP_KEY_VALUE (
    echo [2/4] Gerando a chave local da aplicacao...
    php artisan key:generate --force --ansi
    if errorlevel 1 exit /b 1
) else (
    echo [2/4] Chave local ja configurada.
)

echo [3/4] Preparando o banco didatico...
php artisan qualityshop:backup --no-interaction
if errorlevel 1 exit /b 1
php artisan migrate --seed --force --ansi
if errorlevel 1 exit /b 1

echo [4/4] Limpando configuracoes antigas...
php artisan optimize:clear --ansi
if errorlevel 1 exit /b 1

echo.
echo [SUCESSO] QualityShop configurado.
echo Execute iniciar.bat para abrir o laboratorio em http://127.0.0.1:8000
exit /b 0
