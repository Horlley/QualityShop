@echo off
rem Este arquivo configura somente o terminal chamador; nao altera o Windows.
if defined QUALITYSHOP_PHP goto :verify
if exist "%~dp0..\.runtime_php85\php.exe" set "QUALITYSHOP_PHP=%~dp0..\.runtime_php85\php.exe"
if defined QUALITYSHOP_PHP goto :verify
for /f "delims=" %%P in ('where php.exe 2^>nul') do if not defined QUALITYSHOP_PHP set "QUALITYSHOP_PHP=%%P"
:verify
if not defined QUALITYSHOP_PHP (
    echo [ERRO] PHP ausente. Instale PHP 8.5 e Composer 2. Habilite pdo_sqlite.
    exit /b 1
)
if not exist "%QUALITYSHOP_PHP%" (
    echo [ERRO] QUALITYSHOP_PHP deve apontar para um php.exe existente.
    exit /b 1
)
"%QUALITYSHOP_PHP%" -r "exit(PHP_VERSION_ID >= 80401 ? 0 : 1);"
if errorlevel 1 (
    echo [ERRO] As dependencias exigem PHP 8.4.1 ou superior. Edicao validada com PHP 8.5.10.
    echo Configure QUALITYSHOP_PHP com o caminho completo do php.exe 8.5.
    exit /b 1
)
for %%P in ("%QUALITYSHOP_PHP%") do set "PATH=%%~dpP;%PATH%"
exit /b 0
