@echo off
setlocal EnableExtensions
cd /d "%~dp0"
call "%~dp0ambiente.bat"
if errorlevel 1 exit /b 1
php artisan test --compact
exit /b %errorlevel%
