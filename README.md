# 个人导航网站

[English](README_EN.md)

一个基于 Flight PHP + Tailwind CSS + Alpine.js + Vite 的轻量级个人导航网站。

## 功能特性

- ✅ 轻量级：Flight PHP 框架，内存占用极低
- ✅ 响应式设计：Tailwind CSS 移动端友好
- ✅ 渐进增强：Alpine.js 实现交互，无需重型前端框架
- ✅ 用户认证：简单安全的登录系统
- ✅ 分类管理：支持分类的增删改查，删除前自动检查关联链接
- ✅ 链接管理：支持 VPN 标记，图标自动抓取 / 手动上传
- ✅ 搜索筛选：快速查找链接，支持按"无图标"等状态筛选
- ✅ 数据统计：后台 Dashboard 实时展示链接、分类及异常状态
- ✅ 导入导出：JSON 原生备份及浏览器 HTML 书签导入，支持预览与选择性入库
- ✅ 审计日志：记录管理员关键操作，确保系统可追溯
- ✅ 单页体验：Hash 路由支持浏览器后退 / 前进

## 技术栈

- **后端**：Flight PHP 3.17 + PHP 8.1+
- **前端**：Tailwind CSS 3.4 + Alpine.js 3.13
- **构建工具**：Vite 5.x
- **数据库**：MySQL 5.7 / 8.0

## 环境要求

| 依赖 | 版本 | 说明 |
|------|------|------|
| PHP | ≥ 8.1 | 需启用 `pdo_mysql`、`fileinfo` 扩展 |
| MySQL | ≥ 5.7 | |
| Node.js | ≥ 16 | 用于构建前端资源及开发热更新 |
| Composer | 最新版 | PHP 依赖管理 |

## 快速开始

项目提供了自动检测环境的启动脚本，推荐优先使用。

### 方式一：预览模式（无需开发服务器）

该模式会构建前端资源后启动 PHP 服务器，适合快速体验项目功能。

**Windows（原生）：**

```batch
start-preview.bat
```

**Linux / macOS / WSL2：**

```bash
chmod +x start-preview.sh
./start-preview.sh
```

脚本会自动安装依赖、构建前端、启动服务器。浏览器打开 `http://127.0.0.1:8100` 即可访问。

> **WSL2 用户注意**：脚本会自动检测 WSL2 环境并绑定到 `0.0.0.0`，确保 Windows 浏览器可以正常访问。

### 方式二：手动安装

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

   ```bash
   cp .env.example .env
   ```

   编辑 `.env` 文件，配置数据库连接信息及管理员账号：

   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=personal_nav
   DB_USERNAME=root
   DB_PASSWORD=your_password

   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=admin123
   ```

   执行数据库初始化：

   ```bash
   php scripts/setup_db.php
   ```

5. **构建前端资源**

   ```bash
   npm run build
   ```

6. **启动服务器**

   | 环境 | 命令 |
   |------|------|
   | Linux / macOS | `php -S 127.0.0.1:8100 -t public` |
   | Windows 原生 | `php -S 127.0.0.1:8100 -t public` |
   | **WSL2** | `php -S 0.0.0.0:8100 -t public` |

   > WSL2 必须绑定 `0.0.0.0`，否则 Windows 浏览器无法访问。

7. **访问**

   - 首页：`http://127.0.0.1:8100`
   - 管理后台：`http://127.0.0.1:8100/admin`
   - 默认账号：`admin` / `admin123`（可在 `.env` 中修改）

## 开发模式

开发时使用 Vite 的热更新功能，修改 CSS/JS 后浏览器自动刷新。

### 使用启动脚本（推荐）

脚本会自动检测平台并选择正确的绑定地址。

**Windows（原生）：**

```batch
start-dev.bat
```

**Linux / macOS / WSL2：**

```bash
chmod +x start-dev.sh
./start-dev.sh
```

### 手动启动

需要同时运行两个服务（两个终端窗口）：

```bash
# 终端 1：Vite 开发服务器（热更新）
npm run dev

# 终端 2：PHP 服务器
# Linux / macOS / Windows 原生：
php -S 127.0.0.1:8100 -t public

# WSL2：
php -S 0.0.0.0:8100 -t public
```

### 服务地址

| 服务 | 普通环境 | WSL2 |
|------|---------|------|
| 网站首页 | `http://127.0.0.1:8100` | `http://127.0.0.1:8100`（宿主机浏览器） |
| 管理后台 | `http://127.0.0.1:8100/admin` | 同上 |
| Vite 开发服务器 | `http://127.0.0.1:5173` | `http://<WSL2-IP>:5173`（脚本自动处理） |

### 热更新说明

| 文件类型 | 路径 | 效果 |
|---------|------|------|
| CSS | `resources/css/app.css` | 浏览器自动刷新 |
| JS | `resources/js/main.js` | 浏览器自动刷新 |
| PHP | `app/**/*.php` | 需手动刷新浏览器 |
| 视图 | `resources/views/*.php` | 需手动刷新浏览器 |

## WSL2 特别说明

WSL2 有独立的网络栈，与 Windows 宿主机不在同一网络命名空间，因此有几个需要注意的地方：

### 为什么 WSL2 需要绑定 `0.0.0.0`？

- WSL2 内的 `127.0.0.1` 指向 WSL2 自身，Windows 浏览器无法直接访问
- 绑定 `0.0.0.0` 后，Windows 可通过 `127.0.0.1:8100` 自动转发访问 WSL2 内的服务

### 为什么 Vite 资源要用 WSL2 IP？

- Vite 开发服务器运行在 WSL2 内部，端口 `5173`
- 从 Windows 浏览器加载 `@vite/client` 和 JS/CSS 模块时，必须使用 WSL2 的虚拟机 IP
- 项目已内置自动检测（`AssetHelper` + CSP 动态构建），无需手动配置

### 推荐做法

直接使用项目提供的启动脚本（`start-dev.sh` / `start-preview.sh`），脚本会自动处理上述所有问题。

如果手动启动，务必：
```bash
php -S 0.0.0.0:8100 -t public   # 不是 127.0.0.1
```

## 项目结构

```
feather-nav/
├── app/                      # 应用代码
│   ├── Controllers/          # 控制器
│   ├── Helpers/              # 帮助类（含 AssetHelper）
│   └── Middleware/           # 中间件
├── config/                   # 配置文件
├── database/                 # 数据库脚本
│   └── init.sql              # 初始化 SQL
├── public/                   # Web 根目录
│   ├── assets/               # 构建产物（npm run build）
│   └── index.php             # 入口文件
├── resources/                # 前端源码
│   ├── css/                  # Tailwind CSS
│   ├── js/                   # JavaScript
│   └── views/                # PHP 视图模板
│       ├── admin/            # 管理后台视图
│       ├── auth/             # 登录相关
│       ├── home.php          # 首页
│       └── layout.php        # 公共布局
├── scripts/                  # 工具脚本
│   └── setup_db.php          # 数据库初始化
├── storage/                  # 运行时存储
│   └── logs/                 # 日志
├── start-dev.sh              # 开发模式启动（Linux/macOS/WSL2）
├── start-dev.bat             # 开发模式启动（Windows）
├── start-preview.sh          # 预览模式启动（Linux/macOS/WSL2）
├── start-preview.bat         # 预览模式启动（Windows）
├── .env.example              # 环境变量模板
├── composer.json             # PHP 依赖
├── package.json              # 前端依赖
└── vite.config.js            # Vite 配置
```

## 部署到生产环境

1. **构建前端资源**

   ```bash
   npm run build
   ```

2. **配置 Web 服务器**

   将网站根目录指向 `public/`，确保 URL 重写正确配置：

   - **Apache**：`.htaccess` 已内置
   - **Nginx**：参考以下配置

   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

3. **配置环境变量**

   ```bash
   cp .env.example .env
   ```

   修改生产环境配置，确保 `APP_ENV=production`、`APP_DEBUG=false`。

4. **安全建议**

   - 修改默认管理员密码
   - 启用 HTTPS
   - 定期备份数据库

## 常见问题

### WSL2 中启动后浏览器无法访问？

确认 PHP 服务器绑定的是 `0.0.0.0` 而非 `127.0.0.1`。使用项目提供的启动脚本可自动处理。

### WSL2 中页面打开但样式/JS 不加载？

这是 Vite 资源路径问题。确保：
1. 使用最新代码（`AssetHelper` 已内置 WSL2 IP 检测）
2. 浏览器控制台查看 CSP 报错，确认 CSP 允许了正确的 Vite 地址

### 端口被占用？

修改启动脚本中的 `PHP_PORT` 和 `VITE_PORT` 变量，同时更新 `.env` 中的 `APP_URL`。

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request！
