<?php require __DIR__ . '/../../app/Helpers/AssetHelper.php'; ?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? '洲哥导航' ?></title>
    
    <!-- 网站图标 (Favicon) -->
    <link rel="icon" type="image/svg+xml" href="/img/logo.svg">
    <link rel="alternate icon" href="/img/logo.svg">
    
    <!-- 样式和脚本 -->
    <?php if (\App\Helpers\AssetHelper::isDev()): ?>
        <script type="module" src="<?= \App\Helpers\AssetHelper::get('@vite/client') ?>"></script>
        <script type="module" src="<?= \App\Helpers\AssetHelper::get('js/main.js') ?>"></script>
    <?php else: ?>
        <link rel="stylesheet" href="<?= \App\Helpers\AssetHelper::get('css/app.css') ?>">
        <script defer src="<?= \App\Helpers\AssetHelper::get('js/main.js') ?>"></script>
    <?php endif; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- 网站导航条 -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- 网站Logo和标题 -->
                <div class="flex items-center space-x-3">
                    <img src="/img/logo.svg" alt="网站图标" class="w-8 h-8">
                    <div class="flex items-baseline space-x-3">
                        <!-- 网站标题 -->
                        <h1 class="text-xl font-bold text-gray-800">洲哥导航</h1>
                        <!-- 广告语 - 突出技术栈和极简风格 -->
                        <span class="text-sm text-gray-500 font-medium tracking-wide">极简高效 • Flight PHP + Alpine.js 驱动</span>
                    </div>
                </div>

                <!-- 右上角操作区 -->
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <span class="text-sm text-gray-600">欢迎，<?= htmlspecialchars($_SESSION["username"] ?? "管理员") ?></span>
                        <button
                            onclick="logout()"
                            class="px-3 py-1.5 text-sm bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            退出
                        </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>
    
    <!-- 主要内容 -->
    <main>
        <?= $content ?>
    </main>

<script>
function logout() {
    if (confirm("确定要退出登录吗？")) {
        window.location.href = "/auth/logout";
    }
}
</script>
</body>
</html>
