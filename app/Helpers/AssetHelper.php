<?php

declare(strict_types=1);

namespace App\Helpers;

class AssetHelper
{
    private static array $manifest = [];

    public static function isDev(): bool
    {
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
        return $env === 'local' || !file_exists(__DIR__ . '/../../public/assets/.vite/manifest.json');
    }

    public static function get(string $file): string
    {
        // 检查是否为开发环境
        if (self::isDev()) {
            // 开发环境下直接返回Vite开发服务器的URL
            $vitePort = 5173;
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
            
            // 提取端口前的部分作为host
            $host = explode(':', $host)[0] . ':' . $vitePort;
            
            // 如果是 CSS 文件，Vite 会直接处理 JS 导入，不需要单独引入 CSS
            // 但为了兼容性，如果显式请求 CSS，还是返回 CSS 路径
            return $protocol . '://' . $host . '/' . ltrim($file, '/');
        }

        // 生产环境：使用manifest.json
        if (empty(self::$manifest)) {
            $manifestPath = __DIR__ . '/../../public/assets/.vite/manifest.json';
            if (file_exists($manifestPath)) {
                self::$manifest = json_decode(file_get_contents($manifestPath), true);
            }
        }

        $key = $file;
        if (isset(self::$manifest[$key])) {
            $assetPath = self::$manifest[$key]['file'];
            // 文件实际在 public/assets/assets/ 目录下，所以需要添加一个 assets 前缀
            return '/assets/' . ltrim($assetPath, '/');
        }

        // 回退到旧方式
        return '/assets/' . $file;
    }

    public static function css(string $file): string
    {
        return '<link rel="stylesheet" href="' . self::get($file) . '">';
    }

    public static function js(string $file): string
    {
        return '<script defer src="' . self::get($file) . '"></script>';
    }
}
