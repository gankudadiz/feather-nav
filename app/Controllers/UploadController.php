<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;

class UploadController
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/x-icon', 'image/webp', 'image/svg+xml'];
    private const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    private const UPLOAD_DIR = '/uploads/favicons/';

    public function uploadIcon(): void
    {
        if (!isset($_FILES['file'])) {
            Flight::json(['error' => '没有文件被上传'], 400);
            return;
        }

        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            Flight::json(['error' => '上传失败: ' . $this->getUploadErrorMessage($file['error'])], 400);
            return;
        }

        if ($file['size'] > self::MAX_SIZE) {
            Flight::json(['error' => '文件大小不能超过 2MB'], 400);
            return;
        }

        // 简单的 MIME 类型检查
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            Flight::json(['error' => '不支持的文件类型: ' . $mimeType], 400);
            return;
        }

        // 获取扩展名
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($ext)) {
            // 尝试根据 MIME 类型推断
            $ext = $this->getExtensionFromMime($mimeType);
        }

        // 生成安全的文件名
        $filename = md5(uniqid() . time()) . '.' . $ext;
        $relativePath = self::UPLOAD_DIR . $filename;
        $absolutePath = __DIR__ . '/../../public' . $relativePath;

        // 确保目录存在
        if (!file_exists(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
            Flight::json([
                'message' => '上传成功',
                'url' => $relativePath
            ]);
        } else {
            Flight::json(['error' => '保存文件失败'], 500);
        }
    }

    private function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => '文件大小超过 php.ini 限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过 HTML 表单限制',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => 'PHP 扩展停止了文件上传',
            default => '未知错误',
        };
    }

    private function getExtensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/x-icon' => 'ico',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'bin',
        };
    }
}
