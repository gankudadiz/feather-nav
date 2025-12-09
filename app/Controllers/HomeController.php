<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;

class HomeController
{
    public function index(): void
    {
        $content = $this->renderView('home');
        echo $this->renderLayout($content, '我的导航');
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
