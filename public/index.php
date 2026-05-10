<?php

/**
 * JThinkPHP Web 入口文件
 */

define('J_PATH', dirname(__DIR__));

// 加载核心引导类（重构后的路径）
require J_PATH . '/core/Foundation/JThink.php';

use JThink\Core\Foundation\JThink;

// 启动框架
JThink::run();
