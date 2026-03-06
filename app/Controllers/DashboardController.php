<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Database;
use PDO;

class DashboardController
{
    /**
     * 获取统计数据
     */
    public function getStatistics(): void
    {
        $db = Database::getConnection();

        // 1. 总链接数
        $totalLinks = $db->query("SELECT COUNT(*) FROM links")->fetchColumn();

        // 2. VPN 链接数
        $vpnLinks = $db->query("SELECT COUNT(*) FROM links WHERE need_vpn = 1")->fetchColumn();

        // 3. 分类总数
        $totalCategories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

        // 4. 未分类链接数
        $uncategorizedLinks = $db->query("SELECT COUNT(*) FROM links WHERE category_id IS NULL")->fetchColumn();

        // 5. 无图标链接数
        $noIconLinks = $db->query("SELECT COUNT(*) FROM links WHERE icon IS NULL OR icon = ''")->fetchColumn();

        Flight::json([
            'total_links' => (int)$totalLinks,
            'vpn_links' => (int)$vpnLinks,
            'total_categories' => (int)$totalCategories,
            'uncategorized_links' => (int)$uncategorizedLinks,
            'no_icon_links' => (int)$noIconLinks,
            'vpn_percentage' => $totalLinks > 0 ? round(($vpnLinks / $totalLinks) * 100, 1) : 0
        ]);
    }
}
