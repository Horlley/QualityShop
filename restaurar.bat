@echo off
setlocal EnableExtensions
chcp 65001 > nul
cd /d "%~dp0"
call "%~dp0ambiente.bat"
if errorlevel 1 exit /b 1

if not exist ".env" goto :not_configured
if not exist "vendor\autoload.php" goto :not_configured

if /I "%~1"=="--sim" goto :restore

echo.
echo [ATENCAO] Esta operacao apaga os dados locais e recria o laboratorio.
choice /C SN /N /M "Deseja continuar? [S/N] "
if errorlevel 2 exit /b 0

:restore
echo.
echo [QualityShop] Restaurando o banco didatico...
php artisan qualityshop:backup --no-interaction
if errorlevel 1 exit /b 1
php artisan migrate:fresh --seed --force --ansi
if errorlevel 1 exit /b 1

php artisan optimize:clear --ansi
if errorlevel 1 exit /b 1

echo [SUCESSO] Laboratorio restaurado para o estado inicial.
exit /b 0

:not_configured
echo [ERRO] Ambiente ainda nao configurado. Execute configurar.bat primeiro.
exit /b 1
