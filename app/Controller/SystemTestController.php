<?php

namespace App\Controller;

use JThink\Core\Http\Controller;
use JThink\Core\Foundation\JThink;
use JThink\Core\Support\JWT;
use JThink\Core\Support\Validator;
use JThink\Core\Database\DBFactory;
use JThink\Facade\DB;

/**
 * 系统功能自动化测试控制器
 * 
 * 职责：对框架的核心组件（容器、数据库、JWT、验证器、日志等）进行冒烟测试，并输出结果。
 */
class SystemTestController extends Controller {
    
    public function index() {
        $results = [
            'core' => $this->testCore(),
            'database' => $this->testDatabase(),
            'cache' => $this->testCache(),
            'filesystem' => $this->testFileSystem(),
            'auth' => $this->testAuth(),
            'support' => $this->testSupport(),
            'view' => ['status' => 'success', 'message' => 'View engine is running this test.']
        ];

        $this->assign('results', $results);
        $this->assign('title', 'JThinkPHP System Health Check');
        $this->display('system_test');
    }

    /**
     * 测试核心容器与自动加载
     */
    protected function testCore() {
        try {
            $container = JThink::container();
            if (!$container) return ['status' => 'error', 'message' => 'Container not initialized'];
            
            $config = $container->make('config');
            if (empty($config)) return ['status' => 'error', 'message' => 'Config service not found'];

            return ['status' => 'success', 'message' => 'Container and Autoloader are working perfectly.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * 测试数据库连接与查询构造器
     */
    protected function testDatabase() {
        try {
            // 测试 DB 门面
            $time = DB::fetch("SELECT CURRENT_TIMESTAMP as now");
            if (!$time) return ['status' => 'error', 'message' => 'Database connection failed'];

            // 测试 QueryBuilder
            $count = 0;
            try {
                $count = DB::table('migrations')->count();
            } catch (\Exception $e) {
                // Ignore missing table error
            }

            return [
                'status' => 'success', 
                'message' => "Database connected. Found {$count} migrations. Current DB Time: " . ($time['now'] ?? 'unknown')
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()];
        }
    }

    /**
     * 测试 JWT 认证组件
     */
    protected function testAuth() {
        try {
            $payload = ['user_id' => 1, 'role' => 'tester'];
            $token = JWT::createToken($payload);
            $decoded = JWT::decode($token);

            if ($decoded['user_id'] === 1) {
                return ['status' => 'success', 'message' => 'JWT Encoding/Decoding is valid.'];
            }
            return ['status' => 'error', 'message' => 'JWT Data mismatch'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'JWT Error: ' . $e->getMessage()];
        }
    }

    /**
     * 测试验证器与日志
     */
    protected function testSupport() {
        try {
            // 验证器测试
            $data = ['email' => 'invalid-email'];
            $v = Validator::make($data, ['email' => 'required|email']);
            $pass = $v->validate();
            
            // 日志测试
            JThink::logger()->info('System test performed');

            if (!$pass && isset($v->errors()['email'])) {
                return ['status' => 'success', 'message' => 'Validator correctly caught error. Logger initialized.'];
            }
            return ['status' => 'error', 'message' => 'Validator logic failed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Support Component Error: ' . $e->getMessage()];
        }
    }

    /**
     * 测试缓存组件 (Redis)
     */
    protected function testCache() {
        try {
            $redisConfig = JThink::$config['database']['redis'] ?? [];
            if (empty($redisConfig)) {
                return ['status' => 'warning', 'message' => 'Redis configuration not found, skipping.'];
            }
            
            $redis = new \JThink\Core\Database\RedisClient($redisConfig);
            $redis->connect();
            
            $redis->setex('jthink_test_key', 10, 'system_check');
            $val = $redis->get('jthink_test_key');
            
            if ($val === 'system_check') {
                return ['status' => 'success', 'message' => 'Redis cache read/write is operational.'];
            }
            return ['status' => 'error', 'message' => 'Redis cache data mismatch.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Redis Error: ' . $e->getMessage()];
        }
    }

    /**
     * 测试文件系统与目录权限
     */
    protected function testFileSystem() {
        try {
            $storage = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(dirname(dirname(__DIR__))) . '/storage';
            $logsDir = $storage . '/logs';
            $cacheDir = $storage . '/cache';
            
            $msg = [];
            
            if (!is_dir($storage)) {
                return ['status' => 'error', 'message' => "Storage directory ($storage) does not exist."];
            }
            
            $msg[] = is_writable($logsDir) ? "Logs (Writable)" : "Logs (Read-only)";
            $msg[] = is_writable($cacheDir) ? "Cache (Writable)" : "Cache (Read-only)";
            
            return [
                'status' => (is_writable($logsDir) && is_writable($cacheDir)) ? 'success' : 'warning',
                'message' => 'File Permissions: ' . implode(', ', $msg)
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Filesystem Error: ' . $e->getMessage()];
        }
    }
}
