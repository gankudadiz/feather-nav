<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\CategoryController;
use App\Controllers\LinkController;
use App\Controllers\AuthController;
use App\Controllers\UploadController;
use App\Middleware\CsrfMiddleware;

// 通用认证函数
function requireAuth() {
    // 确保session已经启动
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        // 检查是否是 AJAX/API 请求
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            Flight::json(['error' => 'Unauthorized'], 401);
        } else {
            // 使用完整的URL进行重定向
            $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
            Flight::redirect($baseUrl . '/auth/login');
        }
        exit;
    }
}

function validateCsrf() {
    $csrf = new CsrfMiddleware();
    if (!$csrf->validateToken()) {
        Flight::json(['error' => 'CSRF token验证失败'], 403);
        exit;
    }
}

// 首页
Flight::route('/', [HomeController::class, 'index']);

// 认证路由
Flight::group('/auth', function () {
    Flight::route('GET /login', [AuthController::class, 'showLogin']);
    Flight::route('POST /login', [AuthController::class, 'login']);
    Flight::route('GET /logout', [AuthController::class, 'logout']);
});

// 公开 API 路由
Flight::group('/api', function () {
    // 分类 - 公开读取
    Flight::route('GET /categories', [CategoryController::class, 'index']);
    // 链接 - 公开读取
    Flight::route('GET /links', [LinkController::class, 'index']);
});

// 受保护 API 路由（需要认证）
Flight::group('/api', function () {
    // 分类 - 管理
    Flight::route('POST /categories', function() { requireAuth(); validateCsrf(); $c = new CategoryController(); $c->store(); });
    Flight::route('PUT /categories/@id', function($id) { requireAuth(); validateCsrf(); $c = new CategoryController(); $c->update($id); });
    Flight::route('DELETE /categories/@id', function($id) { requireAuth(); validateCsrf(); $c = new CategoryController(); $c->destroy($id); });

    // 链接 - 管理
    Flight::route('POST /links', function() { requireAuth(); validateCsrf(); $l = new LinkController(); $l->store(); });
    Flight::route('PUT /links/@id', function($id) { requireAuth(); validateCsrf(); $l = new LinkController(); $l->update($id); });
    Flight::route('POST /links/@id/icon', function($id) { requireAuth(); validateCsrf(); $l = new LinkController(); $l->refreshIcon($id); });
    Flight::route('DELETE /links/@id', function($id) { requireAuth(); validateCsrf(); $l = new LinkController(); $l->destroy($id); });

    // 文件上传
    Flight::route('POST /upload/icon', function() { requireAuth(); validateCsrf(); $u = new UploadController(); $u->uploadIcon(); });
});

// 管理页面（需要认证）
Flight::route('/admin', function() {
    requireAuth();
    $h = new HomeController();
    $h->admin();
});
