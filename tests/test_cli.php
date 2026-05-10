<?php

/**
 * JThinkPHP CLI 自动化单元测试脚本
 */

define('J_PATH', dirname(__DIR__));
require J_PATH . '/core/Foundation/functions.php';
require J_PATH . '/core/Foundation/JThink.php';

use JThink\Core\Foundation\JThink;
use JThink\Core\Support\JWT;
use JThink\Facade\DB;

// 初始化环境
JThink::defineConstants();
JThink::registerAutoloader();
JThink::loadEnv();
JThink::loadConfig();
JThink::initContainer();
JThink::initDatabase();

echo "========================================\n";
echo "   JThinkPHP CLI Unit Test Suite\n";
echo "========================================\n\n";

$passCount = 0;
$failCount = 0;

function it($description, $callback) {
    global $passCount, $failCount;
    echo "Testing: {$description} ... ";
    try {
        if ($callback()) {
            echo "\033[32m[PASS]\033[0m\n";
            $passCount++;
        } else {
            echo "\033[31m[FAIL]\033[0m\n";
            $failCount++;
        }
    } catch (\Exception $e) {
        echo "\033[31m[ERROR]\033[0m -> " . $e->getMessage() . "\n";
        $failCount++;
    }
}

// --- 开始测试 ---

it("Container Resolution", function() {
    $container = JThink::container();
    return $container instanceof \JThink\Core\Support\Container;
});

it("Autoloading Class", function() {
    $jwt = new JWT();
    return $jwt instanceof JWT;
});

it("Database Connection", function() {
    $res = DB::fetch("SELECT 1 as test");
    return $res['test'] == 1;
});

it("JWT Generation and Verification", function() {
    $token = JWT::encode(['id' => 123]);
    $decoded = JWT::decode($token);
    return $decoded['id'] == 123;
});

it("Config Access via Helper", function() {
    return config('app.name') !== null;
});

it("Helper url() generation", function() {
    return url('test') !== '';
});

// --- 测试结束 ---

echo "\n----------------------------------------\n";
echo "Final Results: \033[32m{$passCount} Passed\033[0m, \033[31m{$failCount} Failed\033[0m\n";
echo "----------------------------------------\n";

if ($failCount > 0) exit(1);
exit(0);
