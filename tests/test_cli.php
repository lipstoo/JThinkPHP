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

it("Redis Connection & Operations", function() {
    $config = JThink::$config['database']['redis'] ?? [];
    if (empty($config)) return false;
    
    $redis = new \JThink\Core\Database\RedisClient($config);
    $redis->connect();
    $redis->set('cli_test', 'ok');
    $val = $redis->get('cli_test');
    $redis->del('cli_test');
    return $val === 'ok';
});

it("JWT Generation and Verification", function() {
    $token = JWT::createToken(['id' => 123]);
    $decoded = JWT::decode($token);
    return $decoded['user_id'] == 123;
});

it("Global Cache Helper (File)", function() {
    cache('cli_helper_test', 'works', 1);
    $val = cache('cli_helper_test');
    cache('cli_helper_test', false); // Delete
    return $val === 'works';
});

it("Complex Validation Logic", function() {
    $data = ['email' => 'test@example.com', 'age' => 25];
    $v = \JThink\Core\Support\Validator::make($data, [
        'email' => 'required|email',
        'age' => 'required'
    ]);
    return $v->validate() === true;
});

it("Config Access & Nesting", function() {
    $name = config('app.name');
    $driver = config('database.default');
    return !empty($name) && !empty($driver);
});

it("Helper url() generation", function() {
    $url = url('api/user');
    return strpos($url, 'api/user') !== false;
});

// --- 测试结束 ---

echo "\n" . str_repeat("-", 40) . "\n";
if ($failCount === 0) {
    echo "\033[32mSUCCESS: All {$passCount} tests passed!\033[0m\n";
} else {
    echo "\033[31mFAILURE: {$passCount} passed, {$failCount} failed.\033[0m\n";
}
echo str_repeat("-", 40) . "\n";

if ($failCount > 0) exit(1);
exit(0);
