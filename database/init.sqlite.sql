-- Feather Nav SQLite 初始化脚本

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    title TEXT NOT NULL,
    url TEXT NOT NULL,
    description TEXT DEFAULT NULL,
    need_vpn INTEGER DEFAULT 0,
    is_private INTEGER DEFAULT 0,
    icon TEXT DEFAULT NULL,
    sort_order INTEGER DEFAULT 0,
    click_count INTEGER DEFAULT 0,
    last_status INTEGER DEFAULT NULL,
    last_check_at TEXT DEFAULT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_links_click_count ON links (click_count);
CREATE INDEX IF NOT EXISTS idx_links_is_private ON links (is_private);
CREATE INDEX IF NOT EXISTS idx_links_need_vpn ON links (need_vpn);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER DEFAULT NULL,
    action TEXT NOT NULL,
    description TEXT,
    ip_address TEXT,
    user_agent TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs (created_at);
CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs (user_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs (action);

CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT NOT NULL UNIQUE,
    setting_value TEXT,
    setting_name TEXT NOT NULL,
    setting_type TEXT NOT NULL DEFAULT 'text',
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_settings_key ON settings (setting_key);

INSERT OR IGNORE INTO categories (id, name, sort_order) VALUES
(1, '常用工具', 1),
(2, '开发文档', 2),
(3, '学习资源', 3);

INSERT OR IGNORE INTO links (category_id, title, url, description, need_vpn, sort_order) VALUES
(1, 'Google', 'https://www.google.com', '搜索引擎', 1, 1),
(1, 'GitHub', 'https://github.com', '代码托管平台', 0, 2),
(1, '百度', 'https://www.baidu.com', '中文搜索引擎', 0, 3),
(2, 'PHP 文档', 'https://www.php.net/manual/zh/', 'PHP 官方中文文档', 0, 1),
(2, 'MDN', 'https://developer.mozilla.org/zh-CN/', 'Web 开发文档', 1, 2),
(2, 'Stack Overflow', 'https://stackoverflow.com', '程序员问答社区', 1, 3),
(3, '慕课网', 'https://www.imooc.com', 'IT 技能学习平台', 0, 1),
(3, 'YouTube', 'https://www.youtube.com', '视频分享平台', 1, 2);

INSERT OR IGNORE INTO settings (setting_key, setting_value, setting_name, setting_type) VALUES
('site_title', '我的个人导航', '网站主标题', 'text'),
('site_subtitle', '简约而不简单', '网站副标题/一言', 'text'),
('site_keywords', '个人导航,导航站,自定义效率工具', 'SEO关键词', 'text'),
('is_public', '1', '首页公开访问', 'boolean'),
('links_per_page', '12', '首页每页显示链接数', 'number');
