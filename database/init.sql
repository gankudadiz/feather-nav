-- 个人导航网站数据库初始化脚本
-- 兼容 MySQL 5.7 和 8.0

-- 分类表
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 链接表
CREATE TABLE IF NOT EXISTS `links` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED,
    `title` VARCHAR(100) NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `need_vpn` TINYINT(1) DEFAULT 0 COMMENT '是否需要翻墙：0-不需要，1-需要',
    `icon` VARCHAR(500) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建 need_vpn 索引
CREATE INDEX `idx_need_vpn` ON `links` (`need_vpn`);

-- 用户表（单用户）
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入示例数据
INSERT IGNORE INTO `categories` (`id`, `name`, `sort_order`) VALUES
(1, '常用工具', 1),
(2, '开发文档', 2),
(3, '学习资源', 3);

INSERT IGNORE INTO `links` (`category_id`, `title`, `url`, `description`, `need_vpn`, `sort_order`) VALUES
(1, 'Google', 'https://www.google.com', '搜索引擎', 1, 1),
(1, 'GitHub', 'https://github.com', '代码托管平台', 0, 2),
(1, '百度', 'https://www.baidu.com', '中文搜索引擎', 0, 3),
(2, 'PHP 文档', 'https://www.php.net/manual/zh/', 'PHP 官方中文文档', 0, 1),
(2, 'MDN', 'https://developer.mozilla.org/zh-CN/', 'Web 开发文档', 1, 2),
(2, 'Stack Overflow', 'https://stackoverflow.com', '程序员问答社区', 1, 3),
(3, '慕课网', 'https://www.imooc.com', 'IT 技能学习平台', 0, 1),
(3, 'YouTube', 'https://www.youtube.com', '视频分享平台', 1, 2);
