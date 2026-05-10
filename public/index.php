<?php

// 调试模式：开启报错
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * JThinkPHP Web 入口文件
 */

define('J_PATH', dirname(__DIR__));

// 加载核心引导类（重构后的路径）
require J_PATH . '/core/Foundation/JThink.php';

use JThink\Core\Foundation\JThink;

// 启动框架
JThink::run();
