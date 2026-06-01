<?php

declare(strict_types=1);

namespace App\Helpers;

class AssetHelper
{
    private static array $manifest = [];
    private static ?string $localIp = null;

    /**
     * 判断是否为 WSL2 环境
     */
    private static function isWsl(): bool
    {
        static $result = null;
        if ($result === null) {
            $result = file_exists('/proc/sys/fs/binfmt_misc/WSLInterop')
                || stripos(php_uname('r'), 'microsoft') !== false
                || stripos(php_uname('r'), 'WSL') !== false;
        }
        return $result;
    }

    /**
     * 获取本机IP（WSL2环境下返回虚拟机IP，使宿主机浏览器能访问Vite开发服务器；其他环境返回127.0.0.1）
     */
    private static function getLocalIp(): string
    {
        if (self::$localIp === null) {
            // 先尝试从 SERVER_ADDR 获取（服务器实际绑定的地址）
            $ip = $_SERVER['SERVER_ADDR'] ?? '';
            if ($ip && $ip !== '127.0.0.1' && $ip !== '::1' && $ip !== '0.0.0.0' && filter_var($ip, FILTER_VALIDATE_IP)) {
                self::$localIp = $ip;
                return self::$localIp;
            }
            // 仅在 WSL2 环境下通过 hostname -I 获取虚拟机实际IP
            // 非 WSL2 环境（普通Linux/Mac/Windows原生）直接使用127.0.0.1即可
            if (self::isWsl()) {
                $ip = trim(shell_exec('hostname -I 2>/dev/null | awk \'{print $1}\''));
                if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                    self::$localIp = $ip;
                    return self::$localIp;
                }
            }
            self::$localIp = '127.0.0.1';
        }
        return self::$localIp;
    }

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
            // WSL2环境下使用虚拟机实际IP，而非127.0.0.1，使Windows浏览器能访问
            $host = self::getLocalIp() . ':' . $vitePort;
            
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
