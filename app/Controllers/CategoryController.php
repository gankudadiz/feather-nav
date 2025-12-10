<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;

class CategoryController
{
    public function index(): void
    {
        $db = Flight::db()->getConnection();
        $stmt = $db->query('SELECT * FROM categories ORDER BY sort_order ASC');
        $categories = $stmt->fetchAll();

        Flight::json($categories);
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

        if (empty($data['name'])) {
            Flight::json(['error' => '分类名称不能为空'], 400);
            return;
        }

        if (isset($data['sort_order']) && !is_numeric($data['sort_order'])) {
            Flight::json(['error' => '排序必须是数字'], 400);
            return;
        }

        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('INSERT INTO categories (name, sort_order) VALUES (?, ?)');
        $stmt->execute([$data['name'], $data['sort_order'] ?? 0]);

        Flight::json(['id' => $db->lastInsertId(), 'message' => '创建成功'], 201);
    }

    public function update(string $id): void
    {
        $data = Flight::request()->data->getData();

        if (empty($data['name'])) {
            Flight::json(['error' => '分类名称不能为空'], 400);
            return;
        }

        if (isset($data['sort_order']) && !is_numeric($data['sort_order'])) {
            Flight::json(['error' => '排序必须是数字'], 400);
            return;
        }

        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('UPDATE categories SET name = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['sort_order'] ?? 0, $id]);

        Flight::json(['message' => '更新成功']);
    }

    public function destroy(string $id): void
    {
        $db = Flight::db()->getConnection();
        $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);

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

                $stmt = $db->prepare('UPDATE categories SET sort_order = ? WHERE id = ?');
                $stmt->execute([$update['sort_order'], $update['id']]);
            }

            $db->commit();
            Flight::json(['success' => true, 'message' => '批量更新成功']);
        } catch (\Exception $e) {
            $db->rollBack();
            Flight::json(['error' => '更新失败: ' . $e->getMessage()], 500);
        }
    }
}
