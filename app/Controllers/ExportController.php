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
