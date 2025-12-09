<?php

declare(strict_types=1);

namespace App\Helpers;

class FaviconHelper
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const TIMEOUT = 10;
    private const SAVE_DIR = '/uploads/favicons/';

    public static function fetchAndSave(string $targetUrl): ?string
    {
        // 确保URL有协议
        if (!preg_match('~^https?://~i', $targetUrl)) {
            $targetUrl = 'http://' . $targetUrl;
        }

        $parsedUrl = parse_url($targetUrl);
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return null;
        }
        
        $domain = $parsedUrl['host'];
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $baseUrl = $scheme . '://' . $domain;

        // 1. 尝试从HTML中解析 (更准确)
        $iconUrls = self::findIconsFromHtml($targetUrl, $baseUrl);
        
        // 尝试下载解析到的图标
        foreach ($iconUrls as $iconUrl) {
            $result = self::downloadImage($iconUrl);
            if ($result) {
                return $result;
            }
        }

        // 2. 如果没找到或下载失败，回退到默认位置 /favicon.ico
        $defaultIconUrl = $baseUrl . '/favicon.ico';
        // 验证是否存在并尝试下载
        if (self::checkUrlExists($defaultIconUrl)) {
            return self::downloadImage($defaultIconUrl);
        }

        return null;
    }

    /**
     * @return string[]
     */
    private static function findIconsFromHtml(string $url, string $baseUrl): array
    {
        $html = self::curlGet($url);
        if (!$html) {
            return [];
        }

        $icons = [];

        // 匹配 <link rel="icon" ...>
        // 优化正则以支持更多属性顺序和格式
        $patterns = [
            // 标准格式：rel 在 href 之前
            '/<link[^>]+rel=["\'](?:shortcut\s+)?icon["\'][^>]+href=["\']([^"\']+)["\']/i',
            // 反转格式：href 在 rel 之前
            '/<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\'](?:shortcut\s+)?icon["\']/i',
            // 匹配 apple-touch-icon 作为备选
            '/<link[^>]+rel=["\']apple-touch-icon["\'][^>]+href=["\']([^"\']+)["\']/i',
            '/<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\']apple-touch-icon["\']/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $href) {
                    $fullUrl = self::resolveUrl($href, $baseUrl);
                    if (!in_array($fullUrl, $icons)) {
                        $icons[] = $fullUrl;
                    }
                }
            }
        }

        return $icons;
    }

    private static function resolveUrl(string $href, string $baseUrl): string
    {
        if (preg_match('~^https?://~i', $href)) {
            return $href;
        }
        if (str_starts_with($href, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?? 'http';
            return $scheme . ':' . $href;
        }
        if (str_starts_with($href, '/')) {
            return $baseUrl . $href;
        }
        // 相对路径，简单处理为追加到baseUrl后面 (实际上应该处理当前路径)
        return $baseUrl . '/' . $href;
    }

    private static function checkUrlExists(string $url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 400;
    }

    private static function curlGet(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content === false ? null : $content;
    }

    private static function downloadImage(string $url): ?string
    {
        $content = self::curlGet($url);
        if (!$content) {
            return null;
        }

        // 获取扩展名
        $path = parse_url($url, PHP_URL_PATH);
        $ext = $path ? pathinfo($path, PATHINFO_EXTENSION) : '';
        if (!$ext || strlen($ext) > 4) {
            $ext = 'ico';
        }
        
        $filename = md5($url . time()) . '.' . $ext;
        $relativePath = self::SAVE_DIR . $filename;
        $absolutePath = __DIR__ . '/../../public' . $relativePath;

        // 确保目录存在
        if (!file_exists(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        if (file_put_contents($absolutePath, $content)) {
            return $relativePath;
        }

        return null;
    }
}
