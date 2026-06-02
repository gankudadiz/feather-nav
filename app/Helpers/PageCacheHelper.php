<?php
declare(strict_types=1);

namespace App\Helpers;

use Redis;
use RuntimeException;

/**
 * 页面整页缓存助手
 * 
 * 将页面完整 HTML 缓存到 Redis，减少数据库查询和视图渲染时间。
 * Redis 不可用时自动降级为直接渲染，不影响功能。
 */
class PageCacheHelper
{
    /** Redis key 前缀 */
    private const PREFIX = 'nav:page:';

    /**
     * 读取缓存，未命中则回调渲染并写入缓存
     *
     * @param string   $key      缓存键（不含前缀）
     * @param int      $ttl      缓存有效期（秒）
     * @param callable $callback 渲染回调，返回 HTML 字符串
     * @return string HTML 内容
     */
    public static function remember(string $key, int $ttl, callable $callback): string
    {
        $redis = self::connect();
        if ($redis === null) {
            // Redis 不可用时降级到直接渲染，不影响功能
            return (string) $callback();
        }

        $fullKey = self::PREFIX . $key;
        $cached = $redis->get($fullKey);
        if ($cached !== false) {
            return $cached;
        }

        $html = (string) $callback();
        $redis->setex($fullKey, $ttl, $html);
        return $html;
    }

    /**
     * 删除指定缓存
     *
     * @param string $key 缓存键（不含前缀）
     */
    public static function forget(string $key): void
    {
        $redis = self::connect();
        $redis?->del(self::PREFIX . $key);
    }

    /**
     * 连接 Redis，失败返回 null
     */
    private static function connect(): ?Redis
    {
        try {
            $redis = new Redis();
            $redis->connect(
                $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                (int)($_ENV['REDIS_PORT'] ?? 6379),
                1.0
            );
            $db = (int)($_ENV['REDIS_DB'] ?? 0);
            if ($db > 0) {
                $redis->select($db);
            }
            return $redis;
        } catch (\Throwable $e) {
            error_log('Redis 连接失败：' . $e->getMessage());
            return null;
        }
    }
}
