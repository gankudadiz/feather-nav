@echo off
setlocal
chcp 65001 >nul

echo === Personal Navigation Preview Script ===
echo.

REM Check PHP
php --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP not found.
    pause
    exit /b 1
)

REM Check Node.js
node --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Node.js not found.
    pause
    exit /b 1
)

REM Check Composer
call composer --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Composer not found.
    pause
    exit /b 1
)

echo [INFO] Environment check passed.
echo.

REM Check dependencies
if not exist "vendor" (
    echo [INFO] Installing PHP dependencies...
    call composer install
    if errorlevel 1 (
        echo [ERROR] Composer install failed.
        pause
        exit /b 1
    )
)

if not exist "node_modules" (
    echo [INFO] Installing frontend dependencies...
    call npm install
    if errorlevel 1 (
        echo [ERROR] npm install failed.
        pause
        exit /b 1
    )
)

REM Check .env
if not exist ".env" (
    echo [INFO] Creating environment configuration...
    copy .env.example .env
    echo [INFO] Please configure database in .env file.
)

REM Build frontend
echo [INFO] Building frontend assets...
call npm run build
if errorlevel 1 (
    echo [ERROR] Build failed.
    pause
    exit /b 1
)

REM Start server
echo.
echo [INFO] Starting preview server...
echo URL: http://localhost:8080
echo Admin: http://localhost:8080/admin
echo Credentials: See .env file (Default: admin / admin123)
echo.
echo First run? Make sure to run: php scripts/setup_db.php
echo.
echo Press Ctrl+C to stop server.
echo.

php -S localhost:8080 -t public

if errorlevel 1 (
    echo [ERROR] Server exited unexpectedly.
    pause
)

pause
endlocal
