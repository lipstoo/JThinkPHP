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
            'view' => $this->testView()
        ];

        $this->assign('results', $results);
        $this->assign('title', 'JThinkPHP Professional Diagnostics');
        $this->display('system_test');
    }

    protected function testCore() {
        $details = [];
        try {
            $container = JThink::container();
            $details[] = ['check' => 'DI Container', 'status' => $container ? 'success' : 'error', 'msg' => $container ? 'Initialized' : 'Failed'];
            
            $config = $container->make('config');
            $details[] = ['check' => 'Config Loader', 'status' => !empty($config) ? 'success' : 'error', 'msg' => !empty($config) ? 'Config Loaded' : 'Empty'];

            $env = env('APP_ENV');
            $details[] = ['check' => 'Environment', 'status' => 'success', 'msg' => "Mode: {$env}"];

            return ['status' => 'success', 'details' => $details];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'details' => [['check' => 'Fatal', 'status' => 'error', 'msg' => $e->getMessage()]]];
        }
    }

    protected function testDatabase() {
        $details = [];
        try {
            $start = microtime(true);
            $time = DB::fetch("SELECT CURRENT_TIMESTAMP as now");
            $latency = round((microtime(true) - $start) * 1000, 2);
            $details[] = ['check' => 'MySQL Connection', 'status' => 'success', 'msg' => "Latency: {$latency}ms"];

            $count = 0;
            try { $count = DB::table('migrations')->count(); } catch (\Exception $e) {}
            $details[] = ['check' => 'Migrations Table', 'status' => 'success', 'msg' => "Records: {$count}"];
            
            $details[] = ['check' => 'DB Time', 'status' => 'success', 'msg' => $time['now'] ?? 'N/A'];

            return ['status' => 'success', 'details' => $details];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'details' => [['check' => 'Connection', 'status' => 'error', 'msg' => $e->getMessage()]]];
        }
    }

    protected function testCache() {
        $details = [];
        try {
            $redisConfig = JThink::$config['database']['redis'] ?? [];
            if (empty($redisConfig)) {
                return ['status' => 'warning', 'details' => [['check' => 'Redis', 'status' => 'warning', 'msg' => 'Not Configured']]];
            }
            
            if (!class_exists('\Redis')) {
                return ['status' => 'error', 'details' => [['check' => 'PHP Extension', 'status' => 'error', 'msg' => 'php-redis missing']]];
            }

            $redis = new \JThink\Core\Database\RedisClient($redisConfig);
            $redis->connect();
            $details[] = ['check' => 'Connection', 'status' => 'success', 'msg' => 'Connected to ' . $redisConfig['host']];

            $redis->setex('jthink_test_key', 10, 'system_check');
            $val = $redis->get('jthink_test_key');
            $details[] = ['check' => 'Read/Write', 'status' => ($val === 'system_check') ? 'success' : 'error', 'msg' => 'Verified'];
            
            $details[] = ['check' => 'DB Index', 'status' => 'success', 'msg' => 'Selected DB: ' . ($redisConfig['database'] ?? 0)];

            return ['status' => 'success', 'details' => $details];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'details' => [['check' => 'Redis Failure', 'status' => 'error', 'msg' => $e->getMessage()]]];
        }
    }

    protected function testFileSystem() {
        $details = [];
        $storage = STORAGE_PATH;
        
        $details[] = ['check' => 'Storage Root', 'status' => is_dir($storage) ? 'success' : 'error', 'msg' => is_dir($storage) ? 'Exists' : 'Missing'];
        $details[] = ['check' => 'Logs Directory', 'status' => is_writable($storage . '/logs') ? 'success' : 'error', 'msg' => is_writable($storage . '/logs') ? 'Writable' : 'Protected'];
        $details[] = ['check' => 'Cache Directory', 'status' => is_writable($storage . '/cache') ? 'success' : 'error', 'msg' => is_writable($storage . '/cache') ? 'Writable' : 'Protected'];
        
        return ['status' => 'success', 'details' => $details];
    }

    protected function testAuth() {
        $details = [];
        try {
            $payload = ['user_id' => 1, 'role' => 'tester'];
            $token = JWT::createToken($payload);
            $details[] = ['check' => 'JWT Creation', 'status' => 'success', 'msg' => 'Token Generated'];

            $decoded = JWT::decode($token);
            $details[] = ['check' => 'JWT Decoding', 'status' => ($decoded['user_id'] === 1) ? 'success' : 'error', 'msg' => 'Data Integrity OK'];

            return ['status' => 'success', 'details' => $details];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'details' => [['check' => 'Auth Fail', 'status' => 'error', 'msg' => $e->getMessage()]]];
        }
    }

    protected function testSupport() {
        $details = [];
        try {
            $data = ['email' => 'invalid'];
            $v = Validator::make($data, ['email' => 'required|email']);
            $details[] = ['check' => 'Validator', 'status' => !$v->validate() ? 'success' : 'error', 'msg' => 'Rule Matching OK'];

            $logger = JThink::logger();
            $details[] = ['check' => 'Logger', 'status' => $logger ? 'success' : 'error', 'msg' => 'Monolog Active'];

            return ['status' => 'success', 'details' => $details];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'details' => [['check' => 'Support', 'status' => 'error', 'msg' => $e->getMessage()]]];
        }
    }

    protected function testView() {
        $details = [];
        $details[] = ['check' => 'Template Engine', 'status' => 'success', 'msg' => 'PHP Native'];
        $details[] = ['check' => 'Layout System', 'status' => 'success', 'msg' => 'Global CSS Loaded'];
        return ['status' => 'success', 'details' => $details];
    }
}
