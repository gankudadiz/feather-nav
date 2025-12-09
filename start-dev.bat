@echo off
setlocal
chcp 65001 >nul

echo [INFO] Starting development environment...
echo.

REM Check PHP
echo [INFO] Checking PHP...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP not found. Please install PHP and add it to PATH.
    pause
    exit /b 1
)

REM Check Node.js
echo [INFO] Checking Node.js...
call node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Node.js not found. Please install Node.js.
    pause
    exit /b 1
)

REM Check npm
echo [INFO] Checking npm...
call npm --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] npm not found. Please install npm.
    pause
    exit /b 1
)

REM Check Composer
echo [INFO] Checking Composer...
call composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Composer not found. Please install Composer.
    pause
    exit /b 1
)

echo [INFO] Environment check passed.
echo.

REM Check .env
if not exist ".env" (
    echo [WARNING] .env file not found.
    if exist ".env.example" (
        echo [INFO] Creating .env from .env.example...
        copy .env.example .env >nul
        echo [INFO] Please configure .env and run 'php scripts/setup_db.php' to initialize database.
    ) else (
        echo [ERROR] .env.example not found.
    )
    echo.
)

echo [INFO] Checking dependencies...

REM Install PHP dependencies
if not exist "vendor" (
    echo [INFO] Installing PHP dependencies...
    call composer install
    if errorlevel 1 (
        echo [ERROR] Composer install failed.
        pause
        exit /b 1
    )
)

REM Install Node.js dependencies
if not exist "node_modules" (
    echo [INFO] Installing Node.js dependencies...
    call npm install
    if errorlevel 1 (
        echo [ERROR] npm install failed.
        pause
        exit /b 1
    )
)

REM Create log directory
if not exist "storage\logs" mkdir storage\logs

echo [INFO] Starting services...
echo =======================================
echo PHP Server: http://localhost:8080
echo Vite Server: http://localhost:5173
echo =======================================
echo Press Ctrl+C to stop all services
echo.

REM Start Vite server (in background)
echo [INFO] Starting Vite...
start "Vite Server" /MIN cmd /c "npm run dev"

REM Wait for Vite to start
timeout /t 3 /nobreak >nul

REM Start PHP server
echo [INFO] Starting PHP Server...
php -S localhost:8080 -t public

if errorlevel 1 (
    echo [ERROR] PHP Server exited with error.
    pause
)

echo [INFO] Services stopped.
endlocal
