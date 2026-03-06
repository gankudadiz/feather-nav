<?php

declare(strict_types=1);

namespace App\Helpers;

use Flight;
use PDO;

/**
 * 超轻量级审计日志助手类
 */
class LogHelper
{
    /**
     * 记录审计日志
     *
     * @param string $action 动作类型 (如: login_success, link_create)
     * @param string $description 详细描述
     * @param int|null $userId 可选，显式指定用户ID
     * @return void
     */
    public static function log(string $action, string $description = '', ?int $userId = null): void
    {
        try {
            // 获取数据库连接
            $db = Flight::db()->getConnection();

            // 自动补全用户信息
            if ($userId === null) {
                // 如果没传，尝试从 Session 获取
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $userId = $_SESSION['user_id'] ?? null;
            }

            // 获取客户端信息
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

            // 写入日志
            $stmt = $db->prepare('
                INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $userId,
                $action,
                $description,
                $ipAddress,
                mb_substr($userAgent, 0, 255) // 截断过长的 UA
            ]);

            // 概率执行自动清理（每100次记录执行一次，清理90天前的日志）
            if (mt_rand(1, 100) === 1) {
                self::cleanup();
            }
        } catch (\Exception $e) {
            // 日志系统不能影响业务主流程，异常仅记入 PHP 错误日志
            error_log('Audit Log Error: ' . $e->getMessage());
        }
    }

    /**
     * 清理 90 天前的旧日志
     *
     * @param int $days 保留的天数
     * @return void
     */
    public static function cleanup(int $days = 90): void
    {
        try {
            $db = Flight::db()->getConnection();
            $stmt = $db->prepare('DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)');
            $stmt->execute([$days]);
        } catch (\Exception $e) {
            error_log('Audit Log Cleanup Error: ' . $e->getMessage());
        }
    }
}
