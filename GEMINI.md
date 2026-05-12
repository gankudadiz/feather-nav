# GEMINI.md - 个人导航网站项目指令集

本文件为 Gemini CLI 提供项目的上下文信息、架构说明及开发指南。

## 项目概况 (Project Overview)

本项目是一个轻量级的个人导航网站，旨在提供简洁、高效的链接管理服务。

- **核心架构**: 基于 **Flight PHP (v3.0+)** 的微型 MVC 架构。
- **前端技术栈**: 
  - **Tailwind CSS**: 响应式 UI 设计。
  - **Alpine.js**: 轻量级交互逻辑。
  - **Vite**: 静态资源构建与开发热更新。
- **数据库**: MySQL 5.7/8.0。
- **关键特性**:
  - 链接与分类管理（增删改查、排序）。
  - 图标自动抓取及手动上传。
  - 用户认证与安全保护（CSRF、Session）。
  - 详细的操作审计日志。
  - 后台数据统计看板（Dashboard）。

## 运行与构建 (Building and Running)

### 环境依赖
- **PHP**: 8.1+ (需启用 `pdo_mysql`, `fileinfo` 扩展)。
- **Node.js**: 16+ (用于前端构建)。
- **MySQL**: 5.7+。

### 核心命令
- **安装依赖**:
  - PHP: `composer install`
  - 前端: `npm install`
- **数据库初始化**:
  - `php scripts/setup_db.php` (会根据 `.env` 配置自动创建库表并初始化管理员)。
- **开发模式**:
  - 前端热更新: `npm run dev`
  - PHP 服务: `php -S 127.0.0.1:8100 -t public`
- **生产构建**:
  - `npm run build` (产出物位于 `public/assets/`)

## 开发规范与约定 (Development Conventions)

### 1. 目录结构说明
- `app/`: 后端核心逻辑。
  - `Controllers/`: 业务逻辑处理，返回 JSON 或 渲染视图。
  - `Middleware/`: 包含认证 (`AuthMiddleware`) 和 CSRF 校验 (`CsrfMiddleware`)。
- `resources/`: 前端源码。
  - `views/`: PHP 视图模板。`admin/` 目录下按功能拆分为组件、模态框和标签页。
- `public/`: 静态入口及构建产物。

### 2. 安全机制
- **CSRF 保护**: 
  - 所有非 GET 请求必须携带 CSRF Token。
  - Token 可通过 `HTTP_X_CSRF_TOKEN` 请求头或 `csrf_token` 表单字段传递。
  - 后端验证通过 `validateCsrf()` 函数（在 `Routes.php` 中调用）。
- **认证管理**: 
  - 管理功能需通过 `requireAuth()` 拦截，检查 `$_SESSION['user_id']`。

### 3. 前端交互规范 (Alpine.js)
- 后台管理逻辑集中在 `public/js/admin/main.js` 的 `adminInit()` 函数中。
- **Hash 路由**: 后台支持通过 URL Hash（如 `#statistics`, `#links`）切换标签页，支持浏览器前进/后退。
- **异步请求**: 优先使用 `fetch` 进行 API 交互，成功后手动调用 `loadData()` 或 `loadStatistics()` 更新 UI。

### 4. 数据库规范
- 见 `database/init.sql`。
- 链接 (`links`) 关联分类 (`categories`)，采用 `ON DELETE SET NULL`。
- 关键操作需记入 `audit_logs` 表。

### 5. 常见任务操作
- **新增 API**: 在 `app/Routes.php` 中注册路由，并在 `app/Controllers/` 下创建对应处理函数。
- **新增后台标签页**:
  1. 在 `resources/views/admin/tabs/` 创建 `.php` 文件。
  2. 在 `admin.php` 的导航栏和内容区引用。
  3. 在 `main.js` 的 `validTabs` 列表中添加该标签 ID。

---
*注：在执行涉及数据库变更或文件全量删除的操作前，请务必向用户确认。*
