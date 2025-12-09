<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;

class AuthController
{
    public function showLogin(): void
    {
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">登录</h1>
        <form method="POST" action="/auth/login">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                <input type="text" name="username" required
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">密码</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                登录
            </button>
        </form>
    </div>
</body>
</html>';
    }

    public function login(): void
    {
        $username = Flight::request()->data->username ?? '';
        $password = Flight::request()->data->password ?? '';

        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // 确保session已经启动
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // 使用完整的URL进行重定向
            $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
            Flight::redirect($baseUrl . '/admin');
        } else {
            $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
            Flight::redirect($baseUrl . '/auth/login?error=1');
        }
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
        Flight::redirect($baseUrl . '/');
    }
}
