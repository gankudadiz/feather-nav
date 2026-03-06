<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;

class AuditLogController
{
    /**
     * 获取审计日志列表 (仅限管理员)
     */
    public function index(): void
    {
        $db = Flight::db()->getConnection();
        
        // 获取最近的 100 条记录，按时间倒序
        $stmt = $db->query('
            SELECT al.*, u.username 
            FROM audit_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT 100
        ');
        
        $logs = $stmt->fetchAll();
        
        Flight::json($logs);
    }
}
