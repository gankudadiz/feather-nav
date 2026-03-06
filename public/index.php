<?php

declare(strict_types=1);

// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// 加载配置
$config = require __DIR__ . '/../config/app.php';

// 初始化数据库
require __DIR__ . '/../config/database.php';

// 注册路由
require __DIR__ . '/../app/routes.php';

// 配置视图路径
Flight::set('flight.views.path', __DIR__ . '/../resources/views');

// 启动应用前配置全局 Session 安全属性
$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// 注册全局安全响应头
Flight::after('start', function () {
    $env = $_ENV['APP_ENV'] ?? 'production';
    $isLocal = ($env === 'local');

    // Content-Security-Policy (CSP)
    // script-src 添加 'unsafe-eval' 是因为 Alpine.js 需要执行 eval 风格的代码执行
    // connect-src 允许本地 Vite WebSocket
    $csp = "default-src 'self'; ";
    $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com " . ($isLocal ? "http://localhost:5173" : "") . "; ";
    $csp .= "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com " . ($isLocal ? "http://localhost:5173" : "") . "; ";
    $csp .= "img-src 'self' data: https: " . ($isLocal ? "http://localhost:5173" : "") . "; ";
    $csp .= "font-src 'self' data: https:; ";
    $csp .= "connect-src 'self' " . ($isLocal ? "ws://localhost:5173 http://localhost:5173" : "") . ";";

    header("Content-Security-Policy: $csp");

    // 防止点击劫持
    header('X-Frame-Options: SAMEORIGIN');

    
    // 禁用 MIME 嗅探
    header('X-Content-Type-Options: nosniff');
    
    // 启用 XSS 保护（针对旧版浏览器）
    header('X-XSS-Protection: 1; mode=block');
    
    // 强制跳转 HTTPS (仅生产环境建议开启，这里暂不强制 HSTS)
});

// 启动应用
Flight::start();
