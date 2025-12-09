<?php

declare(strict_types=1);

namespace App\Helpers;

class FaviconHelper
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private const TIMEOUT = 10;
    private const CONNECT_TIMEOUT = 3; // 连接超时设置为3秒
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
            $result = self::downloadImage($defaultIconUrl);
            if ($result) {
                return $result;
            }
        }

        // 3. 终极兜底：尝试使用 Google Favicon API (后端代理下载)
        // 注意：这要求服务器能访问 Google
        $googleApiUrl = "https://www.google.com/s2/favicons?domain={$domain}&sz=128";
        $result = self::downloadImage($googleApiUrl);
        if ($result) {
            return $result;
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content === false ? null : $content;
    }

    public static function downloadImage(string $url): ?string
    {
        $content = self::curlGet($url);
        if (!$content) {
            return null;
        }

        // 验证是否为有效的图片内容
        if (!self::isValidImage($content)) {
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

    private static function isValidImage(string $content): bool
    {
        // 1. 快速排除 HTML 文档
        $head = substr(trim($content), 0, 100);
        if (stripos($head, '<!DOCTYPE') !== false || stripos($head, '<html') !== false) {
            return false;
        }

        // 2. 尝试使用 getimagesizefromstring (支持 JPG, PNG, GIF, WEBP, ICO 等)
        if (function_exists('getimagesizefromstring')) {
            $info = @getimagesizefromstring($content);
            if ($info !== false) {
                return true;
            }
        }

        // 3. SVG 特殊处理
        if (str_contains($content, '<svg') && str_contains($content, '</svg>')) {
             try {
                 $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
                 if ($xml !== false && $xml->getName() === 'svg') {
                     return true;
                 }
             } catch (\Throwable $e) {
                 // ignore
             }
        }
        
        // 4. ICO 手动检查 (以防 getimagesizefromstring 失败)
        // ICO header: 2 bytes reserved (0), 2 bytes type (1=ico), 2 bytes count
        if (strlen($content) >= 4 && substr($content, 0, 4) === "\x00\x00\x01\x00") {
            return true;
        }

        return false;
    }
}
