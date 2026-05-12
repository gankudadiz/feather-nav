<?php

declare(strict_types=1);

namespace App\Controllers;

use Flight;
use App\Helpers\LogHelper;

class ExportController
{
    /**
     * 获取全量系统数据（组织为嵌套格式）
     *
     * @return array [ 'categories' => [...], 'uncategorized_links' => [...] ]
     */
    private function getFullExportData(): array
    {
        $db = Flight::db()->getConnection();

        // 1. 获取所有分类
        $stmtCats = $db->query('SELECT id, name, sort_order FROM categories ORDER BY sort_order ASC');
        $categories = $stmtCats->fetchAll(\PDO::FETCH_ASSOC);

        // 2. 获取所有链接
        $stmtLinks = $db->query('SELECT id, category_id, title, url, description, need_vpn, icon, sort_order, created_at FROM links ORDER BY sort_order ASC');
        $allLinks = $stmtLinks->fetchAll(\PDO::FETCH_ASSOC);

        $parsedCategories = [];
        $uncategorizedLinks = [];

        // 将链接关联到对应分类
        $linksByCategoryId = [];
        foreach ($allLinks as $link) {
            unset($link['id']); // 导出数据不要暴露主键 ID，以防将来导入冲突影响
            $catId = $link['category_id'];
            unset($link['category_id']);

            if ($catId) {
                $linksByCategoryId[$catId][] = $link;
            } else {
                $uncategorizedLinks[] = $link;
            }
        }

        foreach ($categories as $cat) {
            $catId = $cat['id'];
            unset($cat['id']);
            $cat['links'] = $linksByCategoryId[$catId] ?? [];
            $parsedCategories[] = $cat;
        }

        return [
            'categories' => $parsedCategories,
            'uncategorized_links' => $uncategorizedLinks
        ];
    }

    /**
     * 导出为 JSON 格式
     */
    public function exportJson(): void
    {
        $exportData = [
            'version' => '1.0',
            'export_date' => date('Y-m-d H:i:s'),
            'data' => $this->getFullExportData()
        ];

        $jsonStr = json_encode($exportData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $filename = 'feather_nav_export_' . date('Ymd_His') . '.json';

        // 记录审计日志
        LogHelper::log('export_json', "执行了系统数据导出 (JSON格式)");

        // 设置下载 Headers
        Flight::response()
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', (string) strlen($jsonStr));

        echo $jsonStr;
        exit;
    }

    /**
     * 获取备份时间
     */
    private function getBackupTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * 获取文件数量
     */
    private function getFileCount(string $dir): int
    {
        $count = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 导出为 SQL 备份格式
     */
    public function exportSql(): void
    {
        $db = Flight::db()->getConnection();
        $dbName = getenv('DB_DATABASE') ?: 'personal_nav';
        $sql = "";

        // 文件头
        $sql .= "-- Feather Nav 数据库备份\n";
        $sql .= "-- 生成时间: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- 数据库: {$dbName}\n\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        // 获取所有表
        $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // 表结构
            $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $sql .= "-- ----------------------------\n";
            $sql .= "-- Table structure for {$table}\n";
            $sql .= "-- ----------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTable['Create Table'] . ";\n\n";

            // 表数据
            $stmt = $db->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "-- ----------------------------\n";
                $sql .= "-- Records of {$table}\n";
                $sql .= "-- ----------------------------\n";

                $columns = array_keys($rows[0]);
                $sql .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";

                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } elseif (is_int($value) || is_float($value)) {
                            $rowValues[] = $value;
                        } else {
                            $rowValues[] = $db->quote((string) $value);
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'feather_nav_backup_' . date('Ymd_His') . '.sql';

        // 记录审计日志
        LogHelper::log('export_sql', "执行了数据库 SQL 备份导出");

        // 清除之前的输出缓冲
        if (ob_get_level()) {
            ob_end_clean();
        }

        // 设置下载 Headers
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sql));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        echo $sql;
        exit;
    }

    /**
     * 导出资源文件为 ZIP 压缩包
     */
    public function exportAssets(): void
    {
        $uploadDir = realpath(__DIR__ . '/../../public/uploads');

        if (!$uploadDir || !is_dir($uploadDir)) {
            Flight::json(['error' => '资源目录不存在'], 404);
            return;
        }

        // 检查是否有文件
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $hasFiles = false;
        foreach ($files as $file) {
            if ($file->isFile()) {
                $hasFiles = true;
                break;
            }
        }

        if (!$hasFiles) {
            Flight::json(['error' => '没有可导出的资源文件'], 404);
            return;
        }

        // 创建 ZIP
        $zip = new \ZipArchive();
        $zipFilename = sys_get_temp_dir() . '/feather_nav_assets_' . date('Ymd_His') . '.zip';

        if ($zip->open($zipFilename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            Flight::json(['error' => '无法创建压缩文件'], 500);
            return;
        }

        // 遍历 uploads 目录添加文件
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            $relativePath = 'uploads/' . substr($filePath, strlen($uploadDir) + 1);
            $zip->addFile($filePath, $relativePath);
        }

        // 添加恢复说明文档
        $readme = <<<README
============================================================
        Feather Nav 资源文件备份恢复说明
============================================================
备份时间: {$this->getBackupTime()}
文件数量: {$this->getFileCount($uploadDir)}
============================================================

【恢复步骤】

1. 上传项目文件到新服务器
   确保项目目录结构完整

2. 解压资源文件
   将本压缩包解压到项目根目录，使文件恢复到 public/uploads/ 目录

3. 设置目录权限
   chmod -R 755 public/uploads
   chown -R www:www public/uploads  (根据实际 web 用户调整)

4. 验证
   访问后台管理页面，检查网站图标是否正常显示

【目录结构】
public/
└── uploads/
    └── favicons/      # 网站图标文件
        ├── xxx.ico
        ├── xxx.png
        └── xxx.jpg

【注意事项】
- 资源文件不纳入版本控制（已在 .gitignore 中排除）
- 必须单独备份和恢复
- 无需创建软链接，直接解压到项目根目录即可

============================================================
README;

        $zip->addFromString('README_恢复说明.txt', $readme);
        $zip->close();

        // 读取 ZIP 内容
        $zipContent = file_get_contents($zipFilename);
        $zipSize = filesize($zipFilename);

        // 删除临时文件
        unlink($zipFilename);

        $filename = 'feather_nav_assets_' . date('Ymd_His') . '.zip';

        // 记录审计日志
        LogHelper::log('export_assets', "执行了资源文件备份导出");

        // 清除输出缓冲
        if (ob_get_level()) {
            ob_end_clean();
        }

        // 设置下载 Headers
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $zipSize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        echo $zipContent;
        exit;
    }

    /**
     * 导出为 HTML 书签格式 (Netscape Bookmark Format)
     */
    public function exportHtml(): void
    {
        $data = $this->getFullExportData();
        $dateUnix = time();

        // 拼装 Netscape HTML 头部
        $html = <<<HTML
<!DOCTYPE NETSCAPE-Bookmark-file-1>
<!-- This is an automatically generated file.
     It will be read and overwritten.
     DO NOT EDIT! -->
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<TITLE>Bookmarks</TITLE>
<H1>Feather Nav Bookmarks</H1>
<DL><p>
HTML;

        // 渲染分类及链接
        foreach ($data['categories'] as $cat) {
            $html .= "\n    <DT><H3 ADD_DATE=\"{$dateUnix}\" LAST_MODIFIED=\"{$dateUnix}\">" . htmlspecialchars($cat['name']) . "</H3>\n";
            $html .= "    <DL><p>\n";
            foreach ($cat['links'] as $link) {
                // 如果是需要 VPN，也可以在 HTML 自定义属性标明，主流浏览器不认，但导入导回可能用得到
                $vpnAttr = $link['need_vpn'] ? ' DATA-VPN="1"' : '';
                $iconAttr = !empty($link['icon']) ? ' ICON_URI="' . htmlspecialchars($link['icon']) . '"' : '';

                $html .= "        <DT><A HREF=\"" . htmlspecialchars($link['url']) . "\" ADD_DATE=\"{$dateUnix}\"{$vpnAttr}{$iconAttr}>" . htmlspecialchars($link['title']) . "</A>\n";
                // 浏览器标签描述部分
                if (!empty($link['description'])) {
                    $html .= "        <DD>" . htmlspecialchars($link['description']) . "\n";
                }
            }
            $html .= "    </DL><p>\n";
        }

        // 渲染未分类链接 (放最外层)
        if (!empty($data['uncategorized_links'])) {
            $html .= "\n    <DT><H3 ADD_DATE=\"{$dateUnix}\" LAST_MODIFIED=\"{$dateUnix}\">未分类</H3>\n";
            $html .= "    <DL><p>\n";
            foreach ($data['uncategorized_links'] as $link) {
                $vpnAttr = $link['need_vpn'] ? ' DATA-VPN="1"' : '';
                $iconAttr = !empty($link['icon']) ? ' ICON_URI="' . htmlspecialchars($link['icon']) . '"' : '';

                $html .= "        <DT><A HREF=\"" . htmlspecialchars($link['url']) . "\" ADD_DATE=\"{$dateUnix}\"{$vpnAttr}{$iconAttr}>" . htmlspecialchars($link['title']) . "</A>\n";
                if (!empty($link['description'])) {
                    $html .= "        <DD>" . htmlspecialchars($link['description']) . "\n";
                }
            }
            $html .= "    </DL><p>\n";
        }

        $html .= "</DL><p>\n";

        $filename = 'bookmarks_export_' . date('Ymd_His') . '.html';

        LogHelper::log('export_html', "执行了系统书签导出 (HTML格式)");

        Flight::response()
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', (string) strlen($html));

        echo $html;
        exit;
    }
}
