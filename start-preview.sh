#!/bin/bash
set -e

# ============================================================
# 预览启动脚本（生产模式）
# 构建前端资源后启动 PHP 服务器，无需 Vite 开发服务器
# 自动检测 WSL2 环境并绑定到 0.0.0.0
# ============================================================

PHP_PORT=8100

# ---------- 环境检测 ----------
detect_env() {
    if grep -qi microsoft /proc/version 2>/dev/null; then
        echo "wsl"
        return
    fi
    if [ -f /proc/sys/fs/binfmt_misc/WSLInterop ]; then
        echo "wsl"
        return
    fi
    if [ "$(uname -s)" = "Darwin" ]; then
        echo "mac"
        return
    fi
    echo "linux"
}

ENV_TYPE=$(detect_env)

if [ "$ENV_TYPE" = "wsl" ]; then
    PHP_BIND="0.0.0.0"
    WSL_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
else
    PHP_BIND="127.0.0.1"
fi

# ---------- 颜色 ----------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m'

echo -e "${GREEN}${BOLD}=== 个人导航网站 - 预览模式 ===${NC}"
echo ""

# ---------- 依赖检查 ----------
check_command() {
    if ! command -v "$1" &> /dev/null; then
        echo -e "${RED}❌ $1 未安装${NC}"
        exit 1
    fi
}

check_command php
check_command node
check_command composer

echo -e "${GREEN}✅ 环境检查通过${NC}"
echo ""

# ---------- 安装依赖 ----------
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}📦 安装 PHP 依赖...${NC}"
    composer install
fi

if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}📦 安装前端依赖...${NC}"
    npm install
fi

# ---------- 环境配置 ----------
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚙️  创建 .env 配置文件...${NC}"
    cp .env.example .env
    echo "请编辑 .env 文件配置数据库连接后重新运行"
    echo ""
fi

# ---------- 构建前端 ----------
echo -e "${YELLOW}🔨 构建前端资源...${NC}"
npm run build

# ---------- 启动 ----------
echo ""
echo -e "${GREEN}${BOLD}🚀 启动预览服务器${NC}"
echo "======================================="
if [ "$ENV_TYPE" = "wsl" ]; then
    echo -e "运行环境: ${YELLOW}WSL2${NC}"
    echo "访问地址:   http://127.0.0.1:${PHP_PORT}"
    echo "管理后台:   http://127.0.0.1:${PHP_PORT}/admin"
    echo "======================================="
    echo -e "${YELLOW}提示: 从 Windows 浏览器访问请打开 http://127.0.0.1:${PHP_PORT}${NC}"
else
    echo "访问地址:   http://127.0.0.1:${PHP_PORT}"
    echo "管理后台:   http://127.0.0.1:${PHP_PORT}/admin"
    echo "======================================="
fi
echo "默认账号:   admin / admin123 (在 .env 中可修改)"
echo ""
echo "首次运行请确保已执行: php scripts/setup_db.php"
echo ""
echo "按 Ctrl+C 停止服务器"
echo ""

php -S ${PHP_BIND}:${PHP_PORT} -t public
