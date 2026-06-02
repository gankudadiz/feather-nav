<?php
declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\PageCacheHelper;

/**
 * 缓存管理控制器
 * 
 * 提供后台手动清除缓存的功能。
 */
class CacheController
{
    /**
     * 清除首页整页缓存
     * 
     * 鉴权：需登录 + CSRF token 校验（由路由层 requireAuth/validateCsrf 保证）
     */
    public function clearHome(): void
    {
        PageCacheHelper::forget('home');
        Flight::json(['success' => true, 'message' => '首页缓存已清除']);
    }
}
