<?php

declare(strict_types=1);

namespace App\Helpers;

use Flight;

/**
 * 系统设置辅助类
 * 提供全局配置的快速读取与统一管理
 */
class SettingHelper
{
    /**
     * @var array|null 缓存加载的设置项
     */
    private static ?array $cache = null;

    /**
     * 加载所有设置项到缓存
     * 
     * @return void
     */
    private static function load(): void
    {
        if (self::$cache !== null) {
            return;
        }

        try {
            $db = Flight::db()->getConnection();
            $stmt = $db->query('SELECT setting_key, setting_value FROM settings');
            $settings = $stmt->fetchAll();

            self::$cache = [];
            foreach ($settings as $setting) {
                self::$cache[$setting['setting_key']] = $setting['setting_value'];
            }
        } catch (\Exception $e) {
            // 如果表不存在或数据库连接失败，给一个空数组防止重复加载
            self::$cache = [];
        }
    }

    /**
     * 获取指定配置项的值
     * 
     * @param string $key 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();

        if (!isset(self::$cache[$key])) {
            return $default;
        }

        $value = self::$cache[$key];

        // 尝试根据常见的布尔值字符串进行转换
        if ($value === '1' || $value === 'true' || $value === 'on') return true;
        if ($value === '0' || $value === 'false' || $value === 'off') return false;
        
        // 如果是数字字符串且不包含小数点，转为 int
        if (is_numeric($value) && strpos($value, '.') === false) {
            return (int) $value;
        }

        return $value;
    }

    /**
     * 强刷缓存（在后台更新配置后调用）
     * 
     * @return void
     */
    public static function flushCache(): void
    {
        self::$cache = null;
    }
}
