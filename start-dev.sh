#!/bin/bash

echo "启动开发环境..."

# 检查Node.js和npm是否安装
if ! command -v node &> /dev/null; then
    echo "错误: 未找到Node.js，请先安装Node.js"
    exit 1
fi

if ! command -v npm &> /dev/null; then
    echo "错误: 未找到npm，请先安装npm"
    exit 1
fi

# 检查Composer是否安装
if ! command -v composer &> /dev/null; then
    echo "错误: 未找到Composer，请先安装Composer"
    exit 1
fi

echo "检查依赖..."

# 安装PHP依赖
if [ ! -d "vendor" ]; then
    echo "安装PHP依赖..."
    composer install
fi

# 安装Node.js依赖
if [ ! -d "node_modules" ]; then
    echo "安装Node.js依赖..."
    npm install
fi

# 创建日志目录
mkdir -p storage/logs

echo "启动服务..."
echo "======================================="
echo "PHP服务器: http://localhost:8080"
echo "Vite开发服务器: http://localhost:5173"
echo "======================================="
echo "按 Ctrl+C 停止所有服务"
echo ""

# 启动Vite开发服务器（在后台）
npm run dev &
VITE_PID=$!

# 等待一下让Vite启动
sleep 2

# 启动PHP内置服务器
php -S localhost:8080 -t public

# 当PHP服务器停止时，也停止Vite服务器
kill $VITE_PID 2>/dev/null