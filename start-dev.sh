#!/bin/bash
set -e

# ============================================================
# 开发环境启动脚本
# 自动检测运行环境（WSL2 / 普通Linux / Mac），智能选择绑定地址
# ============================================================

PHP_PORT=8100
VITE_PORT=5173

# ---------- 环境检测 ----------
detect_env() {
    # 检测 WSL2
    if grep -qi microsoft /proc/version 2>/dev/null; then
        echo "wsl"
        return
    fi
    if [ -f /proc/sys/fs/binfmt_misc/WSLInterop ]; then
        echo "wsl"
        return
    fi
    # 检测 macOS
    if [ "$(uname -s)" = "Darwin" ]; then
        echo "mac"
        return
    fi
    echo "linux"
}

ENV_TYPE=$(detect_env)

# ---------- 根据环境决定绑定地址 ----------
if [ "$ENV_TYPE" = "wsl" ]; then
    PHP_BIND="0.0.0.0"
    # 获取 WSL2 虚拟机 IP（用于外部浏览器访问 Vite）
    WSL_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
    if [ -z "$WSL_IP" ]; then
        WSL_IP=$(ip route get 1 2>/dev/null | awk '{print $7; exit}')
    fi
    if [ -z "$WSL_IP" ]; then
        WSL_IP="无法检测"
    fi
else
    PHP_BIND="127.0.0.1"
fi

# ---------- 颜色输出 ----------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m'

echo -e "${GREEN}${BOLD}=== 启动开发环境 ===${NC}"
echo ""

# ---------- 依赖检查 ----------
check_command() {
    if ! command -v "$1" &> /dev/null; then
        echo -e "${RED}错误: 未找到 $1，请先安装${NC}"
        exit 1
    fi
}

check_command node
check_command npm
check_command composer
check_command php

# ---------- 安装依赖 ----------
echo -e "${YELLOW}检查依赖...${NC}"

if [ ! -d "vendor" ]; then
    echo "安装 PHP 依赖..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo "安装 Node.js 依赖..."
    npm install
fi

# ---------- 创建必要目录 ----------
mkdir -p storage/logs

# ---------- 启动 ----------
echo ""
echo -e "${GREEN}${BOLD}服务启动中...${NC}"
echo "======================================="

if [ "$ENV_TYPE" = "wsl" ]; then
    echo -e "运行环境: ${YELLOW}WSL2${NC}"
    echo "PHP 服务器:  http://127.0.0.1:${PHP_PORT}  (宿主机浏览器访问)"
    echo "Vite 服务器: http://${WSL_IP}:${VITE_PORT}"
    echo "======================================="
    echo -e "${YELLOW}提示: 从 Windows 浏览器访问请打开 http://127.0.0.1:${PHP_PORT}${NC}"
else
    echo -e "运行环境: ${YELLOW}$([ "$ENV_TYPE" = "mac" ] && echo "macOS" || echo "Linux")${NC}"
    echo "PHP 服务器:  http://127.0.0.1:${PHP_PORT}"
    echo "Vite 服务器: http://127.0.0.1:${VITE_PORT}"
    echo "======================================="
fi

echo "按 Ctrl+C 停止所有服务"
echo ""

# ---------- 清理函数 ----------
cleanup() {
    echo ""
    echo -e "${YELLOW}正在停止服务...${NC}"
    kill $VITE_PID 2>/dev/null
    wait $VITE_PID 2>/dev/null
    echo -e "${GREEN}服务已停止${NC}"
    exit 0
}
trap cleanup SIGINT SIGTERM

# ---------- 启动 Vite 开发服务器 ----------
npm run dev &
VITE_PID=$!
sleep 2

# ---------- 启动 PHP 内置服务器 ----------
php -S ${PHP_BIND}:${PHP_PORT} -t public

# 如果 PHP 意外退出，清理 Vite
kill $VITE_PID 2>/dev/null
