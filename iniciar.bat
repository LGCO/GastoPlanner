@echo off
REM Script para iniciar el servidor PHP en Windows
REM Este archivo inicia un servidor web local para ejecutar la aplicación

setlocal enabledelayedexpansion

echo.
echo ============================================
echo   Calendario Financiero 2026
echo   Iniciando servidor local...
echo ============================================
echo.

REM Verificar si PHP está instalado
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP no está instalado o no está en el PATH del sistema.
    echo.
    echo Por favor:
    echo 1. Descarga PHP desde https://www.php.net/downloads
    echo 2. Instálalo en tu sistema
    echo 3. Agrega la carpeta bin de PHP al PATH de tu sistema
    echo 4. Reinicia este script
    echo.
    pause
    exit /b 1
)

REM Obtener el directorio actual
cd /d "%~dp0"

REM Iniciar el servidor
echo Abriendo navegador en http://localhost:8000
echo.
echo Presiona CTRL+C para detener el servidor.
echo.

REM Intentar abrir el navegador automáticamente
timeout /t 2 /nobreak
start http://localhost:8000

REM Iniciar servidor PHP
php -S localhost:8000

pause
