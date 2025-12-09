<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\FaviconHelper;

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
            isset($data['need_vpn']) ? (int)$data['need_vpn'] : 0,
            $data['icon'] ?? null,
            $data['sort_order'] ?? 0
        ]);

        Flight::json(['id' => $db->lastInsertId(), 'message' => '创建成功'], 201);
    }

    public function update(string $id): void
    {
        $data = Flight::request()->data->getData();

        if (empty($data['title']) || empty($data['url'])) {
            Flight::json(['error' => '标题和URL不能为空'], 400);
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
            'UPDATE links SET category_id = ?, title = ?, url = ?, description = ?, need_vpn = ?, icon = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['category_id'] ?: null,
            $data['title'],
            $data['url'],
            $data['description'] ?? null,
            isset($data['need_vpn']) ? (int)$data['need_vpn'] : 0,
            $data['icon'] ?? null,
            $data['sort_order'] ?? 0,
            $id
        ]);

        Flight::json(['message' => '更新成功']);
    }

    public function destroy(string $id): void
    {
        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('DELETE FROM links WHERE id = ?');
        $stmt->execute([$id]);

        Flight::json(['message' => '删除成功']);
    }

    public function refreshIcon(string $id): void
    {
        // 验证权限后立即关闭 Session 写入，释放锁，避免阻塞其他并发请求
        session_write_close();

        $db = Flight::db()->getConnection();
        
        // 1. 获取链接信息
        $stmt = $db->prepare('SELECT url FROM links WHERE id = ?');
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

        Flight::json(['message' => '图标更新成功', 'icon' => $icon]);
    }
}
