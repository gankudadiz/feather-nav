#!/bin/bash

echo "=== 个人导航网站预览脚本 ==="
echo ""

# 检查 PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP 未安装"
    exit 1
fi

# 检查 Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js 未安装"
    exit 1
fi

# 检查 Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer 未安装"
    exit 1
fi

echo "✅ 环境检查通过"
echo ""

# 检查依赖
if [ ! -d "vendor" ]; then
    echo "📦 安装 PHP 依赖..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo "📦 安装前端依赖..."
    npm install
fi

# 检查 .env
if [ ! -f ".env" ]; then
    echo "⚙️  创建环境配置文件..."
    cp .env.example .env
    echo "请编辑 .env 文件配置数据库连接"
fi

# 构建前端资源
echo "🔨 构建前端资源..."
npm run build

# 启动服务器
echo ""
echo "🚀 启动预览服务器..."
echo "访问地址：http://localhost:8080"
echo "管理后台：http://localhost:8080/admin"
echo "登录账号：请查看 .env 文件 (默认: admin / admin123)"
echo ""
echo "首次运行请确保已执行：php scripts/setup_db.php"
echo ""
echo "按 Ctrl+C 停止服务器"
echo ""

php -S localhost:8080 -t public
