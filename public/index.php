<?php

declare(strict_types=1);

// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// 加载配置
$config = require __DIR__ . '/../config/app.php';

// 初始化数据库
require __DIR__ . '/../config/database.php';

// 注册路由
require __DIR__ . '/../app/routes.php';

// 配置视图路径
Flight::set('flight.views.path', __DIR__ . '/../resources/views');

// 启动应用
Flight::start();
