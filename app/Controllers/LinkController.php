<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\FaviconHelper;
use App\Helpers\LogHelper;

class LinkController
{
    public function batchDestroy(): void
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (empty($data['ids']) || !is_array($data['ids'])) {
            Flight::json(['error' => '无效的 ID 列表'], 400);
            return;
        }

        $db = Flight::db()->getConnection();
        $db->beginTransaction();

        try {
            $ids = array_filter($data['ids'], 'is_numeric');
            if (empty($ids)) {
                throw new \Exception('没有有效的 ID');
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("DELETE FROM links WHERE id IN ($placeholders)");
            $stmt->execute($ids);

            $count = count($ids);
            $db->commit();
            LogHelper::log('link_batch_delete', "批量删除链接 (共 $count 个)");
            Flight::json(['success' => true, 'message' => "成功删除 $count 条链接"]);
        } catch (\Exception $e) {
            $db->rollBack();
            Flight::json(['error' => '删除失败: ' . $e->getMessage()], 500);
        }
    }

    public function batchMove(): void
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (empty($data['ids']) || !is_array($data['ids']) || !isset($data['category_id'])) {
            Flight::json(['error' => '参数不完整'], 400);
            return;
        }

        $db = Flight::db()->getConnection();
        $db->beginTransaction();

        try {
            $ids = array_filter($data['ids'], 'is_numeric');
            if (empty($ids)) {
                throw new \Exception('没有有效的 ID');
            }

            $categoryId = $data['category_id'] ?: null;
            
            // 校验分类是否存在
            if ($categoryId !== null) {
                $checkCat = $db->prepare('SELECT name FROM categories WHERE id = ?');
                $checkCat->execute([$categoryId]);
                $categoryName = $checkCat->fetchColumn();
                if (!$categoryName) {
                    throw new \Exception('目标分类不存在');
                }
            } else {
                $categoryName = '未分类';
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("UPDATE links SET category_id = ? WHERE id IN ($placeholders)");
            
            $params = array_merge([$categoryId], $ids);
            $stmt->execute($params);

            $count = count($ids);
            $db->commit();
            LogHelper::log('link_batch_move', "批量转移链接至 [$categoryName] (共 $count 个)");
            Flight::json(['success' => true, 'message' => "成功转移 $count 条链接至 $categoryName"]);
        } catch (\Exception $e) {
            $db->rollBack();
            Flight::json(['error' => '移动失败: ' . $e->getMessage()], 500);
        }
    }

    public function index(): void
    {
        $db = Flight::db()->getConnection();
        $stmt = $db->query('SELECT id, category_id, title, url, description, need_vpn, icon, sort_order, click_count, last_status, last_check_at, created_at FROM links ORDER BY sort_order ASC');
        $links = $stmt->fetchAll();

        Flight::json($links);
    }

    public function store(): void
    {
        $request = Flight::request();

        // 支持表单数据和JSON数据
        $data = $request->data->getData();

        // 如果没有数据且是JSON请求，则解析原始数据
        if (empty($data) && $request->header('Content-Type') === 'application/json') {
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true) ?: [];
        }

        if (empty($data['title']) || empty($data['url'])) {
            Flight::json(['error' => '标题和URL不能为空'], 400);
            return;
        }


        // Validate URL
        if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            Flight::json(['error' => 'URL格式无效'], 400);
            return;
        }

        // Validate Category ID
        if (!empty($data['category_id']) && !is_numeric($data['category_id'])) {
            Flight::json(['error' => '无效的分类ID'], 400);
            return;
        }

        // 如果提供了图标URL且不是本地上传的，尝试下载并保存到本地
        if (!empty($data['icon']) && !str_starts_with($data['icon'], '/uploads/favicons/')) {
            $localIcon = FaviconHelper::downloadImage($data['icon']);
            if ($localIcon) {
                $data['icon'] = $localIcon;
            }
        }

        $db = Flight::db()->getConnection();
        $stmt = $db->prepare(
            'INSERT INTO links (category_id, title, url, description, need_vpn, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['category_id'] ?: null,
            $data['title'],
            $data['url'],
            $data['description'] ?? null,
            isset($data['need_vpn']) ? (int) $data['need_vpn'] : 0,
            $data['icon'] ?? null,
            $data['sort_order'] ?? 0
        ]);

        $newId = $db->lastInsertId();
        LogHelper::log('link_create', "添加链接: {$data['title']} (ID: $newId)");

        Flight::json(['id' => $newId, 'message' => '创建成功'], 201);
    }

    public function update(string $id): void
    {
        $request = Flight::request();

        // 支持表单数据和JSON数据
        $data = $request->data->getData();

        // 如果没有数据且是JSON请求，则解析原始数据
        if (empty($data) && $request->header('Content-Type') === 'application/json') {
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true) ?: [];
        }

        if (empty($data['title']) || empty($data['url'])) {
            Flight::json(['error' => '标题和URL不能为空'], 400);
            return;
        }

        // Validate URL
        if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            Flight::json(['error' => 'URL格式无效'], 400);
            return;
        }

        // Validate Category ID
        if (!empty($data['category_id']) && !is_numeric($data['category_id'])) {
            Flight::json(['error' => '无效的分类ID'], 400);
            return;
        }

        // Validate Link ID
        if (!is_numeric($id)) {
            Flight::json(['error' => '无效的链接ID'], 400);
            return;
        }

        // 如果提供了图标URL且不是本地上传的，尝试下载并保存到本地
        if (!empty($data['icon']) && !str_starts_with($data['icon'], '/uploads/favicons/')) {
            $localIcon = FaviconHelper::downloadImage($data['icon']);
            if ($localIcon) {
                $data['icon'] = $localIcon;
            }
        }

        $db = Flight::db()->getConnection();

        // 检查记录是否存在
        $checkStmt = $db->prepare('SELECT id FROM links WHERE id = ?');
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetchColumn()) {
            Flight::json(['error' => '未找到该链接'], 404);
            return;
        }

        $stmt = $db->prepare(
            'UPDATE links SET category_id = ?, title = ?, url = ?, description = ?, need_vpn = ?, icon = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['category_id'] ?: null,
            $data['title'],
            $data['url'],
            $data['description'] ?? null,
            isset($data['need_vpn']) ? (int) $data['need_vpn'] : 0,
            $data['icon'] ?? null,
            $data['sort_order'] ?? 0,
            $id
        ]);

        LogHelper::log('link_update', "更新链接: {$data['title']} (ID: $id)");

        Flight::json(['message' => '更新成功']);
    }

    public function destroy(string $id): void
    {
        if (!is_numeric($id)) {
            Flight::json(['error' => '无效的链接ID'], 400);
            return;
        }

        $db = Flight::db()->getConnection();

        // 获取名称以便记录日志，同时检查记录是否存在
        $nameStmt = $db->prepare('SELECT title FROM links WHERE id = ?');
        $nameStmt->execute([$id]);
        $linkTitle = $nameStmt->fetchColumn();

        if ($linkTitle === false) {
            Flight::json(['error' => '未找到该链接'], 404);
            return;
        }

        $stmt = $db->prepare('DELETE FROM links WHERE id = ?');
        $stmt->execute([$id]);

        LogHelper::log('link_delete', "删除链接: $linkTitle (ID: $id)");

        Flight::json(['message' => '删除成功']);
    }

    public function batchUpdate(): void
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (empty($data['updates']) || !is_array($data['updates'])) {
            Flight::json(['error' => '无效的更新数据'], 400);
            return;
        }

        $db = Flight::db()->getConnection();
        $db->beginTransaction();

        try {
            foreach ($data['updates'] as $update) {
                if (!isset($update['id']) || !isset($update['sort_order'])) {
                    throw new \Exception('缺少必要参数：id 或 sort_order');
                }

                $stmt = $db->prepare('UPDATE links SET sort_order = ? WHERE id = ?');
                $stmt->execute([$update['sort_order'], $update['id']]);
            }

            $db->commit();
            LogHelper::log('link_batch_reorder', "批量调整链接排序 (共 " . count($data['updates']) . " 个)");
            Flight::json(['success' => true, 'message' => '批量更新成功']);
        } catch (\Exception $e) {
            $db->rollBack();
            Flight::json(['error' => '更新失败: ' . $e->getMessage()], 500);
        }
    }

    public function refreshIcon(string $id): void
    {
        if (!is_numeric($id)) {
            Flight::json(['error' => '无效的链接ID'], 400);
            return;
        }

        // 验证权限后立即关闭 Session 写入，释放锁，避免阻塞其他并发请求
        session_write_close();

        $db = Flight::db()->getConnection();

        // 1. 获取链接信息
        $stmt = $db->prepare('SELECT title, url FROM links WHERE id = ?');
        $stmt->execute([$id]);
        $link = $stmt->fetch();

        if (!$link) {
            Flight::json(['error' => '链接不存在'], 404);
            return;
        }

        // 2. 抓取图标
        $icon = FaviconHelper::fetchAndSave($link['url']);

        if (!$icon) {
            Flight::json(['error' => '未能抓取到图标'], 400);
            return;
        }

        // 3. 更新数据库
        $stmt = $db->prepare('UPDATE links SET icon = ? WHERE id = ?');
        $stmt->execute([$icon, $id]);

        LogHelper::log('link_refresh_icon', "刷新图标: {$link['title']} (ID: $id)");

        Flight::json(['message' => '图标更新成功', 'icon' => $icon]);
    }

    public function recordClick(string $id): void
    {
        if (!is_numeric($id)) {
            Flight::json(['error' => 'Invalid ID'], 400);
            return;
        }

        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('UPDATE links SET click_count = click_count + 1 WHERE id = ?');
        $stmt->execute([$id]);

        Flight::json(['success' => true]);
    }

    public function checkLinkStatus(string $id): void
    {
        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('SELECT id, url FROM links WHERE id = ?');
        $stmt->execute([$id]);
        $link = $stmt->fetch();

        if (!$link) {
            Flight::json(['error' => 'Link not found'], 404);
            return;
        }

        $status = $this->fetchUrlStatus($link['url']);
        
        $updateStmt = $db->prepare('UPDATE links SET last_status = ?, last_check_at = CURRENT_TIMESTAMP WHERE id = ?');
        $updateStmt->execute([$status, $id]);

        Flight::json([
            'id' => $id,
            'url' => $link['url'],
            'status' => $status,
            'checked_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function checkAllLinksStatus(): void
    {
        // 获取前端传来的参数
        $input = Flight::request()->data;
        $includeVpn = isset($input->include_vpn) && $input->include_vpn == true;

        // 设置较长的脚本执行时间
        set_time_limit(600);
        
        $db = Flight::db()->getConnection();
        
        // 根据参数决定是否包含 VPN 链接
        $sql = 'SELECT id, url FROM links';
        if (!$includeVpn) {
            $sql .= ' WHERE need_vpn = 0';
        }
        
        $links = $db->query($sql)->fetchAll();
        
        $results = [];
        $updateStmt = $db->prepare('UPDATE links SET last_status = ?, last_check_at = CURRENT_TIMESTAMP WHERE id = ?');

        foreach ($links as $link) {
            $status = $this->fetchUrlStatus($link['url']);
            $updateStmt->execute([$status, $link['id']]);
            $results[] = [
                'id' => $link['id'],
                'status' => $status
            ];
        }

        Flight::json([
            'message' => 'Checked ' . count($links) . ' links' . ($includeVpn ? ' (including VPN)' : ' (skipped VPN)'),
            'count' => count($links),
            'results' => $results
        ]);
    }

    /**
     * 使用 cURL 获取 URL 状态码
     */
    private function fetchUrlStatus(string $url): int
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10秒超时
        curl_setopt($ch, CURLOPT_NOBODY, true); // 仅获取头部，不下载内容
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略 SSL 证书错误
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            // 如果 cURL 出错（如 DNS 失败，连接超时），返回 0
            $status = 0;
        }
        
        curl_close($ch);
        return $status;
    }
}
