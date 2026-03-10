<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\LogHelper;
use App\Helpers\FaviconHelper;
use Exception;

class ImportController
{
    /**
     * 第一阶段：仅解析上传的文件，返回给前端预览
     */
    public function uploadForPreview(): void
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Flight::json(['error' => '文件上传失败或未提供文件'], 400);
            return;
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        try {
            if ($ext === 'json') {
                $content = file_get_contents($file['tmp_name']);
                $parsedData = $this->parseJson($content);
            } elseif ($ext === 'html') {
                $content = file_get_contents($file['tmp_name']);
                $parsedData = $this->parseHtml($content);
            } else {
                throw new Exception("不支持的文件格式：{$ext}");
            }

            @unlink($file['tmp_name']);

            // 将扁平数组转为按分类分组的树形结构，方便前端渲染
            $previewTree = $this->buildPreviewTree($parsedData);

            Flight::json([
                'success' => true,
                'data' => $previewTree,
                'total_links' => count($parsedData)
            ]);

        } catch (Exception $e) {
            @unlink($file['tmp_name']);
            Flight::json(['error' => '解析失败: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 将扁平的链接数组按分类聚合为树
     */
    private function buildPreviewTree(array $parsedData): array
    {
        $tree = [];
        // 为了给前端加唯一ID以便勾选控制，给每条链接加一个临时前端用的id
        $tempId = 1;
        foreach ($parsedData as $link) {
            $cat = empty($link['category']) ? '未分类' : $link['category'];
            if (!isset($tree[$cat])) {
                $tree[$cat] = [
                    'category_name' => $cat,
                    'links' => []
                ];
            }
            $link['_temp_id'] = $tempId++; // 前端防碰撞用的临时ID
            $tree[$cat]['links'][] = $link;
        }
        return array_values($tree);
    }

    /**
     * 第二阶段：接收前端勾选的数据执行终态入库
     */
    public function confirmImport(): void
    {
        set_time_limit(600); // 增加时间限制给后台任务
        ini_set('memory_limit', '256M');

        $body = json_decode(Flight::request()->getBody(), true) ?? [];
        $strategy = $body['strategy'] ?? 'skip';
        $linksToImport = $body['links'] ?? [];

        if (empty($linksToImport) || !is_array($linksToImport)) {
            Flight::json(['error' => '没有选中要导入的数据'], 400);
            return;
        }

        try {
            // 执行入库，并获取需要抓取图标的 URL 列表
            $result = $this->executeImport($linksToImport, $strategy);
            $stats = $result['stats'];
            $urlsToFetch = $result['urls_to_fetch'];

            LogHelper::log('import_data', "通过预览确认导入数据：新增 {$stats['inserted']} 条，覆盖 {$stats['updated']} 条，新建分类 {$stats['new_categories']} 个");

            // 构造响应数据，直接使用标准的 Flight::json 返回给前端，确保瞬间响应
            Flight::json([
                'success' => true,
                'message' => '导入完成',
                'stats' => $stats,
                'urls_to_fetch' => $urlsToFetch
            ]);

        } catch (\Throwable $e) {
            Flight::json(['error' => '导入入库失败: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 前端触发的纯异步后台抓取接口（发后不理）
     */
    public function fetchFavicons(): void
    {
        set_time_limit(600); // 10分钟长时任务
        ignore_user_abort(true); // 重点：允许客户端挂断（不等待/跳转页面），服务器依然继续抓取

        $body = json_decode(Flight::request()->getBody(), true) ?? [];
        $urls = $body['urls'] ?? [];

        if (empty($urls) || !is_array($urls)) {
            Flight::json(['success' => true]);
            return;
        }

        // 如果环境支持，提前发送成功响应并告知 Web Server 关掉客户端连接
        if (function_exists('fastcgi_finish_request')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            fastcgi_finish_request();
        }

        // 开始队列排队抓取
        $this->backgroundFetchFavicons($urls);

        // 如果环境（如内置 php -S）不支持提前收尾，则在跑完所有循环后正常响应（反正前端没有写 await，不会卡死 UI）
        if (!function_exists('fastcgi_finish_request')) {
            Flight::json(['success' => true]);
        }
    }

    /**
     * 后台静默抓取图标并更新数据库
     */
    private function backgroundFetchFavicons(array $urls): void
    {
        $db = Flight::db()->getConnection();
        $updateStmt = $db->prepare('UPDATE links SET icon = ? WHERE url = ? AND (icon IS NULL OR icon = \'\')');

        foreach ($urls as $url) {
            try {
                // 抓取并保存
                $iconPath = FaviconHelper::fetchAndSave($url);
                if ($iconPath) {
                    $updateStmt->execute([$iconPath, $url]);
                }
                // 稍微休息一下，避免对目标网站或本地 CPU 造成太大压力
                usleep(100000); // 100ms
            } catch (\Throwable $e) {
                // 忽略单个抓取失败，不管是 Error 还是 Exception 都不中断循环
                continue;
            }
        }
    }

    /**
     * 解析 JSON 格式数据
     * 返回结构：[['category' => '前端', 'url' => '...', 'title' => '...', 'desc' => '...', 'need_vpn' => 0, 'icon' => '...']]
     */
    private function parseJson(string $jsonStr): array
    {
        $data = json_decode($jsonStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON解析错误');
        }

        $result = [];

        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $cat) {
                $catName = trim($cat['name'] ?? '未分类');
                if (isset($cat['links']) && is_array($cat['links'])) {
                    foreach ($cat['links'] as $link) {
                        $result[] = $this->normalizeLinkData($catName, $link);
                    }
                }
            }
        }

        if (isset($data['uncategorized_links']) && is_array($data['uncategorized_links'])) {
            foreach ($data['uncategorized_links'] as $link) {
                $result[] = $this->normalizeLinkData('未分类', $link);
            }
        }

        return $result;
    }

    /**
     * 根据行解析 HTML 书签格式
     */
    private function parseHtml(string $htmlStr): array
    {
        $lines = explode("\n", $htmlStr);
        $result = [];
        $categoryStack = [];
        $currentCategory = '未分类';

        foreach ($lines as $line) {
            // 匹配分类文件夹
            if (preg_match('/<DT><H3[^>]*>(.*?)<\/H3>/i', $line, $matches)) {
                $currentCategory = trim($matches[1]);
                $categoryStack[] = $currentCategory; // 这里简化处理，可以压栈，不过由于通常嵌套书签只有一层 H3 被读取，直接赋当前值也行
            }
            // 匹配 <DL> 标签结束以出栈 (不过大部分情况我们只需要最新的H3)
            elseif (preg_match('/<\/DL>/i', $line)) {
                array_pop($categoryStack);
                $currentCategory = !empty($categoryStack) ? end($categoryStack) : '未分类';
            }
            // 匹配书签链接
            elseif (preg_match('/<DT><A HREF="([^"]+)"[^>]*>(.*?)<\/A>/i', $line, $matches)) {
                // 尝试提取 ICON (Base64 或 URL) 
                $icon = '';
                if (preg_match('/ICON="([^"]+)"/i', $line, $iconMatches)) {
                    $icon = $iconMatches[1];
                }

                $url = trim($matches[1]);
                $title = trim($matches[2]);

                // 初步跳过一些内置的脚本协议
                if (str_starts_with(strtolower($url), 'javascript:') || str_starts_with(strtolower($url), 'place:')) {
                    continue;
                }

                $result[] = [
                    'category' => $currentCategory,
                    'title' => $title ?: $url,
                    'url' => $url,
                    'description' => '',
                    'need_vpn' => str_contains($line, 'DATA-VPN="1"') ? 1 : 0,
                    'icon' => $icon
                ];
            }
            // 匹配描述 <DD> （通常跟在 <DT><A> 下面）
            // 我们默认将它赋给上一个抓取到的链接
            elseif (preg_match('/<DD>(.*?)$/i', $line, $matches)) {
                if (!empty($result)) {
                    $lastIdx = count($result) - 1;
                    $result[$lastIdx]['description'] = trim($matches[1]);
                }
            }
        }

        return $result;
    }

    /**
     * 规范化单条链接记录格式
     */
    private function normalizeLinkData(string $category, array $link): array
    {
        return [
            'category' => $category,
            'title' => trim($link['title'] ?? $link['url'] ?? ''),
            'url' => trim($link['url'] ?? ''),
            'description' => trim($link['description'] ?? ''),
            'need_vpn' => isset($link['need_vpn']) ? (int) $link['need_vpn'] : 0,
            'icon' => trim($link['icon'] ?? '')
        ];
    }

    /**
     * 核心入库逻辑
     */
    private function executeImport(array $linksData, string $strategy): array
    {
        $db = Flight::db()->getConnection();

        $stats = [
            'total' => count($linksData),
            'inserted' => 0,
            'skipped' => 0,
            'updated' => 0,
            'new_categories' => 0,
            'failed' => 0
        ];
        $urlsToFetch = [];

        // 缓存分类映射 (名称 => ID)
        $categoryCache = [];
        $stmtCats = $db->query('SELECT id, name FROM categories');
        while ($row = $stmtCats->fetch(\PDO::FETCH_ASSOC)) {
            $categoryCache[$row['name']] = $row['id'];
        }

        // 准备查询语句
        $checkUrlStmt = $db->prepare('SELECT id FROM links WHERE url = ?');
        $insertLinkStmt = $db->prepare('
            INSERT INTO links (category_id, title, url, description, need_vpn, icon, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $updateLinkStmt = $db->prepare('
            UPDATE links 
            SET category_id = ?, title = ?, description = ?, need_vpn = ?, icon = COALESCE(NULLIF(?,\'\'), icon) 
            WHERE url = ?
        ');
        $insertCatStmt = $db->prepare('INSERT INTO categories (name, sort_order) VALUES (?, 0)');

        $db->beginTransaction();

        try {
            foreach ($linksData as $data) {
                if (empty($data['url'])) {
                    $stats['failed']++;
                    continue;
                }

                // 数据长度清洗，防止 SQLSTATE[22001] 报错
                $cleanTitle = mb_substr($data['title'], 0, 100);
                $cleanUrl = mb_substr($data['url'], 0, 500);
                $cleanDesc = mb_substr($data['description'] ?? '', 0, 255);

                $icon = $data['icon'];
                if ($icon && strlen($icon) > 500) {
                    $icon = null;
                }

                $catName = $data['category'] === '未分类' || empty($data['category']) ? null : $data['category'];
                if ($catName) {
                    $catName = mb_substr($catName, 0, 50);
                }
                $categoryId = null;

                // 动态拉取或创建分类
                if ($catName) {
                    if (isset($categoryCache[$catName])) {
                        $categoryId = $categoryCache[$catName];
                    } else {
                        $insertCatStmt->execute([$catName]);
                        $categoryId = $db->lastInsertId();
                        $categoryCache[$catName] = $categoryId;
                        $stats['new_categories']++;
                    }
                }

                if ($icon && str_starts_with($icon, '/uploads/')) {
                    $localPath = __DIR__ . '/../../public' . $icon;
                    if (!file_exists($localPath)) {
                        $icon = null;
                    }
                }

                // 检查 URL 是否已存在
                $checkUrlStmt->execute([$cleanUrl]);
                $existingId = $checkUrlStmt->fetchColumn();

                if ($existingId) {
                    if ($strategy === 'update') {
                        $updateLinkStmt->execute([
                            $categoryId,
                            $cleanTitle,
                            $cleanDesc,
                            $data['need_vpn'],
                            $icon,
                            $cleanUrl
                        ]);
                        $stats['updated']++;

                        // 如果更新后依然没有图标，加入抓取队列
                        if (empty($icon)) {
                            $urlsToFetch[] = $cleanUrl;
                        }
                    } else {
                        $stats['skipped']++;
                    }
                } else {
                    // 插入新记录
                    $insertLinkStmt->execute([
                        $categoryId,
                        $cleanTitle,
                        $cleanUrl,
                        $cleanDesc,
                        $data['need_vpn'],
                        $icon,
                        0
                    ]);
                    $stats['inserted']++;

                    // 如果插入时没有图标，加入抓取队列
                    if (empty($icon)) {
                        $urlsToFetch[] = $cleanUrl;
                    }
                }
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return [
            'stats' => $stats,
            'urls_to_fetch' => array_unique($urlsToFetch)
        ];
    }
}
