@echo off
setlocal EnableExtensions
chcp 65001 > nul
cd /d "%~dp0"
call "%~dp0ambiente.bat"
if errorlevel 1 exit /b 1

if not exist ".env" (
    echo [ERRO] Ambiente ainda nao configurado. Execute configurar.bat primeiro.
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo [ERRO] Dependencias ausentes. Execute configurar.bat primeiro.
    exit /b 1
)

echo.
echo [QualityShop] Laboratorio disponivel em http://127.0.0.1:8000
echo Pressione Ctrl+C para encerrar o servidor.
echo.

php artisan serve --host=127.0.0.1 --port=8000
exit /b %errorlevel%
