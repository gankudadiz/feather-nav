# 个人导航网站

[English](README_EN.md)

一个基于 Flight PHP + Tailwind CSS + Alpine.js 的轻量级个人导航网站。

## 功能特性

- ✅ 轻量级：Flight PHP 框架，内存占用极低
- ✅ 响应式设计：使用 Tailwind CSS
- ✅ 无需复杂前端框架：Alpine.js 实现交互
- ✅ 用户认证：简单安全的登录系统
- ✅ 分类管理：支持多级分类
- ✅ 搜索功能：快速查找链接
- ✅ 图标支持：支持自定义图标，支持自动抓取网站图标

## 技术栈

- **后端**：Flight PHP 3.17 + PHP 8.3
- **前端**：Tailwind CSS 3.4 + Alpine.js 3.13 + Vite 5.0
- **数据库**：MySQL 5.7/8.0
- **构建工具**：Vite

## 快速开始

### 方式一：快速预览（推荐）

如果你只是想快速体验本项目，无需复杂的配置，可以使用我们提供的预览脚本：

**Windows用户：**
```bash
start-preview.bat
```

**Linux/Mac用户：**
```bash
chmod +x start-preview.sh
./start-preview.sh
```

该脚本会自动检测环境、安装依赖、构建前端资源并启动本地预览服务器。

### 方式二：手动安装

#### 环境要求

- PHP 8.1+ (必须启用 `pdo_mysql` 扩展)
- MySQL 5.7+
- Node.js 16+ (开发/构建前端资源)

#### 安装步骤

1. **克隆项目**
   ```bash
   git clone <repository-url>
   cd feather-nav
   ```

2. **安装 PHP 依赖**
   ```bash
   composer install
   ```

3. **安装前端依赖**
   ```bash
   npm install
   ```

4. **配置数据库**
   - 复制配置模板：
     ```bash
     cp .env.example .env
     ```
   - 编辑 `.env` 文件，配置数据库连接及管理员账号密码 (`ADMIN_USERNAME`, `ADMIN_PASSWORD`)
   - 运行初始化脚本：
     ```bash
     php scripts/setup_db.php
     ```

5. **构建前端资源**
   ```bash
   npm run build
   ```

6. **启动开发服务器**
   ```bash
   php -S localhost:8080 -t public
   ```

7. **访问网站**
   - 首页：http://localhost:8080
   - 管理后台：http://localhost:8080/admin
   - 默认登录：admin / admin123

### 开发模式

开发时可以使用 Vite 的热更新功能，支持CSS和JS的实时更新：

#### 方法一：使用启动脚本（推荐）

**Windows用户：**
```bash
start-dev.bat
```

**Linux/Mac用户：**
```bash
chmod +x start-dev.sh
./start-dev.sh
```

#### 方法二：手动启动两个服务

```bash
# 终端1：启动前端开发服务器（支持热更新）
npm run dev

# 终端2：启动 PHP 服务器
php -S localhost:8080 -t public
```

#### 服务地址

- **网站首页**：http://localhost:8080
- **管理后台**：http://localhost:8080/admin
- **Vite开发服务器**：http://localhost:5173（用于前端资源）

#### 热更新说明

- 修改CSS文件：`resources/css/app.css` - 浏览器自动刷新
- 修改JS文件：`resources/js/main.js` - 浏览器自动刷新
- 修改PHP文件：需要手动刷新浏览器
- 修改视图文件：`resources/views/*.php` - 需要手动刷新浏览器

## 项目结构

```
feather-nav/
├── app/                      # 应用代码
│   ├── Controllers/          # 控制器
│   ├── Helpers/             # 帮助类
│   └── Middleware/          # 中间件
├── config/                  # 配置文件
├── database/                # 数据库相关
│   └── init.sql            # 初始化脚本
├── public/                  # Web 根目录
│   ├── assets/             # 静态资源
│   ├── index.php           # 入口文件
│   └── .htaccess           # URL 重写规则
├── resources/              # 前端资源
│   ├── css/               # 样式文件
│   ├── js/                # JavaScript 文件
│   └── views/             # 视图模板
├── storage/               # 存储目录
│   └── logs/             # 日志文件
├── vendor/               # Composer 依赖
├── node_modules/         # NPM 依赖
├── .env                  # 环境变量
├── composer.json         # PHP 依赖配置
└── package.json          # 前端依赖配置
```

## 部署到生产环境

1. **构建前端资源**
   ```bash
   npm run build
   ```

2. **配置服务器**
   - 将 Web 根目录指向 `public/`
   - 确保 `.htaccess` 文件生效（Apache）
   - 或配置 Nginx 重写规则

3. **设置环境变量**
   - 复制 `.env.example` 为 `.env`
   - 修改生产环境配置

4. **安全建议**
   - 修改默认管理员密码
   - 启用 HTTPS
   - 定期备份数据库

## 自定义配置

### 修改默认用户

在 `.env` 中修改 `ADMIN_USERNAME` 和 `ADMIN_PASSWORD`，然后重新运行 `php scripts/setup_db.php`，或者直接在数据库中更新：

```sql
UPDATE users SET password = WHERE username = 'admin';
```

### 添加更多功能

- 主题切换：添加 dark mode 支持
- 导入/导出：支持浏览器书签导入
- 标签系统：为链接添加标签
- 排序功能：自定义分类和链接排序

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request！

## 联系方式

如有问题，请通过 GitHub Issues 反馈。
