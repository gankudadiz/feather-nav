<?php

namespace App\Middleware;

class CsrfMiddleware
{
    /**
     * 生成CSRF token
     */
    public function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * 验证CSRF token
     */
    public function validateToken(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        // 使用时序安全的字符串比较
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * 获取token（供视图使用）
     */
    public function getToken(): string
    {
        return $this->generateToken();
    }
}
