<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Middleware\CsrfMiddleware;
use App\Helpers\CaptchaHelper;

class AuthController
{
    public function showLogin(): void
    {
        $csrf = new CsrfMiddleware();
        $csrfToken = $csrf->getToken();
        Flight::render('auth/login', ['csrfToken' => $csrfToken]);
    }

    public function login(): void
    {
        // 验证CSRF token
        $csrf = new CsrfMiddleware();
        if (!$csrf->validateToken()) {
            $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
            Flight::redirect($baseUrl . '/auth/login?error=csrf');
            return;
        }

        $username = trim(Flight::request()->data->username ?? '');
        $password = Flight::request()->data->password ?? '';
        $captcha = trim(Flight::request()->data->captcha ?? '');

        // Check lock
        if (CaptchaHelper::isLocked($username)) {
            $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
            Flight::redirect($baseUrl . '/auth/login?error=locked');
            return;
        }

        // Check captcha
        if (CaptchaHelper::requiresCaptcha($username)) {
            $sessionCaptcha = $_SESSION['captcha_answer'] ?? null;

            if (empty($captcha) || !is_numeric($captcha) || (int)$captcha !== $sessionCaptcha) {
                CaptchaHelper::recordFailure($username);
                unset($_SESSION['captcha_answer']);

                $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
                Flight::redirect($baseUrl . '/auth/login?error=captcha');
                return;
            }
            unset($_SESSION['captcha_answer']);
        }

        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // 登录成功，清除失败记录
            CaptchaHelper::clearFailures($username);

            // 确保session已经启动
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // 重新生成会话ID（防止会话固定攻击）
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // 使用完整的URL进行重定向
            $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
            Flight::redirect($baseUrl . '/admin');
        } else {
            // 登录失败，记录失败次数
            CaptchaHelper::recordFailure($username);

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
