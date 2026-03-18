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
    `click_count` INT UNSIGNED DEFAULT 0 COMMENT '累计点击次数',
    `last_status` SMALLINT DEFAULT NULL COMMENT '最后一次检测的 HTTP 状态码',
    `last_check_at` TIMESTAMP NULL DEFAULT NULL COMMENT '最后一次检测时间',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_click_count` (`click_count`)
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

-- 超轻量级审计日志表
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL COMMENT '操作者ID，匿名或登录失败为NULL',
    `action` VARCHAR(50) NOT NULL COMMENT '动作类型',
    `description` TEXT COMMENT '详细描述',
    `ip_address` VARCHAR(45) COMMENT 'IP地址',
    `user_agent` VARCHAR(255) COMMENT '浏览器UA',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- 索引优化
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 系统全局设置表
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(50) NOT NULL UNIQUE COMMENT '配置键名',
    `setting_value` TEXT COMMENT '配置值',
    `setting_name` VARCHAR(100) NOT NULL COMMENT '配置显示名称(中文)',
    `setting_type` VARCHAR(20) NOT NULL DEFAULT 'text' COMMENT '输入类型 (text, boolean, number, textarea)',
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统全局设置表';

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

-- 插入初始系统设置
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_name`, `setting_type`) VALUES
('site_title', '我的个人导航', '网站主标题', 'text'),
('site_subtitle', '简约而不简单', '网站副标题/一言', 'text'),
('site_keywords', '个人导航,导航站,自定义效率工具', 'SEO关键词', 'text'),
('is_public', '1', '首页公开访问', 'boolean'),
('links_per_page', '12', '首页每页显示链接数', 'number');

