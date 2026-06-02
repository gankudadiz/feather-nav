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
                $baseUrl = $_ENV['APP_URL'] ?? 'http://127.0.0.1:8100';
                Flight::redirect($baseUrl . '/auth/login');
                exit;
            }
        }

        // 确保 session 已启动（is_public=true 时上一步不会启动 session）
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 已登录用户不缓存，直接渲染（个性化场景留给登录后）
        if (!empty($_SESSION['user_id'])) {
            echo $this->renderAndOutput();
            return;
        }

        // 未登录访客走 Redis 整页缓存
        echo \App\Helpers\PageCacheHelper::remember('home', 21600, function () {
            return $this->renderAndOutput(true);
        });
    }

    /**
     * 渲染首页并返回 HTML
     * 
     * @param bool $return true 时返回 HTML 字符串，false 时直接 echo
     * @return string HTML 内容
     */
    private function renderAndOutput(bool $return = false): string
    {
        $siteTitle = SettingHelper::get('site_title', '我的导航');
        $siteSubtitle = SettingHelper::get('site_subtitle', '简约而不简单');

        $content = $this->renderView('home', [
            'siteTitle' => $siteTitle,
            'siteSubtitle' => $siteSubtitle
        ]);
        $html = $this->renderLayout($content, $siteTitle);

        if ($return) {
            return $html;
        }
        echo $html;
        return '';
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
