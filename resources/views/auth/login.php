<?php
use App\Helpers\CaptchaHelper;
$captcha = CaptchaHelper::generate();
$_SESSION['captcha_answer'] = $captcha['answer'];

$error = $_GET['error'] ?? '';
$errorMessages = [
    '1' => '登录失败，请检查用户名和密码',
    'csrf' => '安全验证失败，请重试',
    'captcha' => '验证码错误',
    'locked' => '登录尝试次数过多，账户已被锁定15分钟'
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                管理员登录
            </h2>
        </div>
        <form class="mt-8 space-y-6" method="POST" action="/auth/login">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                <input id="username" name="username" type="text" required
                       class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                       placeholder="用户名">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">密码</label>
                <input id="password" name="password" type="password" required
                       class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                       placeholder="密码">
            </div>
            
            <div class="mb-6">
                <label for="captcha" class="block text-sm font-medium text-gray-700 mb-1">验证码</label>
                <div class="flex items-center space-x-3">
                    <div class="text-lg font-bold text-gray-900 bg-gray-100 px-4 py-2 rounded min-w-[140px] text-center whitespace-nowrap">
                        <?= htmlspecialchars($captcha['question'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <input id="captcha" name="captcha" type="text"
                           class="appearance-none relative block w-40 px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="计算结果" autocomplete="off">
                    <button type="button" onclick="location.reload()" class="text-sm text-indigo-600 hover:text-indigo-500 whitespace-nowrap px-2">
                        刷新
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">多次登录失败后需要输入验证码</p>
            </div>

            <?php if ($error && isset($errorMessages[$error])): ?>
                <div class="rounded-md bg-red-50 p-4 mb-4">
                    <p class="text-sm text-red-800"><?= htmlspecialchars($errorMessages[$error], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            <?php endif; ?>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    登录
                </button>
            </div>
        </form>
    </div>
</body>
</html>
