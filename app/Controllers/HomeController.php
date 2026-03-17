<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\SettingHelper;

class HomeController
{
    public function index(): void
    {
        // 检查系统是否设置为公开访问
        $isPublic = SettingHelper::get('is_public', true);
        
        // 如果不公开且未登录，重定向到登录页
        if (!$isPublic) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['user_id'])) {
                $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080';
                Flight::redirect($baseUrl . '/auth/login');
                exit;
            }
        }

        $siteTitle = SettingHelper::get('site_title', '我的导航');
        $siteSubtitle = SettingHelper::get('site_subtitle', '简约而不简单');

        $content = $this->renderView('home', [
            'siteTitle' => $siteTitle,
            'siteSubtitle' => $siteSubtitle
        ]);
        echo $this->renderLayout($content, $siteTitle);
    }

    public function admin(): void
    {
        $csrf = new \App\Middleware\CsrfMiddleware();
        $csrfToken = $csrf->getToken();

        $content = $this->renderView('admin', ['csrfToken' => $csrfToken]);
        echo $this->renderLayout($content, '管理后台');
    }

    private function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../../resources/views/' . $view . '.php';
        return ob_get_clean();
    }

    private function renderLayout(string $content, string $title = '个人导航'): string
    {
        ob_start();
        include __DIR__ . '/../../resources/views/layout.php';
        return ob_get_clean();
    }
}
