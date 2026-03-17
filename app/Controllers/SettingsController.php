<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\LogHelper;
use App\Helpers\SettingHelper;

/**
 * 设置控制器
 * 处理系统配置项的读取与更新
 */
class SettingsController
{
    /**
     * 获取所有设置项（供后台管理界面渲染表单）
     * 
     * @return void
     */
    public function index(): void
    {
        $db = Flight::db()->getConnection();
        $stmt = $db->query('SELECT setting_key, setting_value, setting_name, setting_type FROM settings ORDER BY id ASC');
        $settings = $stmt->fetchAll();

        Flight::json($settings);
    }

    /**
     * 批量更新设置项
     * 
     * @return void
     */
    public function update(): void
    {
        $request = Flight::request();
        $data = $request->data->getData();

        // 支持解析 JSON 负载
        if (empty($data) && $request->header('Content-Type') === 'application/json') {
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true) ?: [];
        }

        if (empty($data)) {
            Flight::json(['error' => '没有提供更新数据'], 400);
            return;
        }

        $db = Flight::db()->getConnection();
        $db->beginTransaction();

        try {
            $updateCount = 0;
            $updatedKeys = [];

            foreach ($data as $key => $value) {
                // 校验键名是否存在，防止注入无效配置
                $checkStmt = $db->prepare('SELECT setting_name FROM settings WHERE setting_key = ?');
                $checkStmt->execute([$key]);
                $settingName = $checkStmt->fetchColumn();

                if ($settingName) {
                    $stmt = $db->prepare('UPDATE settings SET setting_value = ? WHERE setting_key = ?');
                    $stmt->execute([$value, $key]);
                    $updateCount++;
                    $updatedKeys[] = $settingName;
                }
            }

            if ($updateCount > 0) {
                $db->commit();
                // 配置更新后强制刷新 Helper 中的缓存
                SettingHelper::flushCache();
                
                $logDetails = "批量更新了 " . $updateCount . " 项设置: " . implode(', ', $updatedKeys);
                LogHelper::log('settings_update', $logDetails);
                
                Flight::json(['success' => true, 'message' => "成功更新 $updateCount 项设置"]);
            } else {
                $db->rollBack();
                Flight::json(['error' => '未找到有效的设置项进行更新'], 400);
            }
        } catch (\Exception $e) {
            $db->rollBack();
            Flight::json(['error' => '更新失败: ' . $e->getMessage()], 500);
        }
    }
}
