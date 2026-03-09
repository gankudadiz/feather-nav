<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\FaviconHelper;
use App\Helpers\LogHelper;

class LinkController
{
    public function index(): void
    {
        $db = Flight::db()->getConnection();
        $stmt = $db->query('SELECT id, category_id, title, url, description, need_vpn, icon, sort_order, created_at FROM links ORDER BY sort_order ASC');
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
}
