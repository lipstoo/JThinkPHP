<?php

namespace JThink\Core\Foundation;

use JThink\Core\Support\Container;
use JThink\Core\Support\ServiceProviderManager;
use JThink\Core\Support\Logger;
use JThink\Core\Http\Router;
use JThink\Core\Http\Session;
use JThink\Core\Http\Request;
use JThink\Core\Http\Response;
use JThink\Core\Database\DBFactory;
use JThink\Core\View\View;

/**
 * JThink 框架引导类
 * 
 * 职责：负责框架的启动流程、环境初始化、核心容器管理及请求分发。
 * 该类是整个框架的入口点，协调各个组件协同工作。
 */
class JThink {
    /** @var array 框架全局配置 */
    public static $config = [];
    
    /** @var array 当前请求的原始数据 */
    public static $request = [];
    
    /** @var Router 路由实例 */
    public static $router = null;
    
    /** @var Container 依赖注入容器实例 */
    public static $container = null;
    
    /** @var Logger 日志管理实例 */
    public static $logger = null;
    
    /** @var View 视图渲染引擎实例 */
    public static $view = null;
    
    /** @var Session 会话管理实例 */
    public static $session = null;
    
    /** @var ServiceProviderManager 服务提供者管理器 */
    protected static $providerManager = null;

    /**
     * 启动框架
     * 按顺序执行初始化任务，并分发请求
     */
    public static function run() {
        try {
            self::defineConstants();     // 1. 定义系统常量
            self::registerAutoloader();  // 2. 注册 PSR-4 自动加载
            self::loadEnv();            // 3. 加载 .env 环境变量
            self::loadConfig();         // 4. 加载配置文件
            self::initContainer();      // 5. 初始化 DI 容器
            self::registerProviders();  // 6. 注册服务提供者
            self::initLogger();         // 7. 初始化日志系统
            self::initSession();        // 8. 初始化会话
            self::initDatabase();       // 9. 初始化数据库工厂
            self::initRedis();          // 10. 初始化 Redis 连接
            self::initView();           // 11. 初始化视图引擎
            self::bootProviders();      // 12. 引导所有服务提供者
            self::parseRequest();       // 13. 解析原始 HTTP 请求
            self::loadRouter();         // 14. 加载路由配置
            self::dispatch();           // 15. 路由分发
        } catch (\Throwable $e) {
            self::handleFatalError($e); // 处理核心启动阶段的致命错误
        }
    }

    /**
     * 定义框架核心路径常量
     */
    public static function defineConstants() {
        if (!defined('J_PATH')) {
            // 注意：由于 JThink.php 现在在 core/Foundation/ 下，获取根目录需要向上两级
            define('J_PATH', dirname(dirname(__DIR__)));
        }
        if (!defined('J_CORE')) {
            define('J_CORE', J_PATH . '/core');
        }
        // 预加载全局助手函数
        require_once J_CORE . '/Foundation/functions.php';
        
        if (!defined('J_APP')) {
            define('J_APP', J_PATH . '/app');
        }
        if (!defined('J_PUBLIC')) {
            define('J_PUBLIC', J_PATH . '/public');
        }
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', J_PATH);
        }
        if (!defined('APP_PATH')) {
            define('APP_PATH', J_APP);
        }
        if (!defined('STORAGE_PATH')) {
            define('STORAGE_PATH', J_PATH . '/storage');
        }
    }

    /**
     * 注册 PSR-4 自动加载逻辑
     * 自动处理归类后的目录结构
     */
    public static function registerAutoloader() {
        spl_autoload_register(function ($class) {
            $prefixes = [
                'JThink\\Core\\' => J_CORE . '/',
                'JThink\\Facade\\' => J_CORE . '/Support/Facades/',
                'JThink\\Middleware\\' => J_CORE . '/Http/Middleware/',
                'App\\' => J_APP . '/',
            ];

            foreach ($prefixes as $prefix => $baseDir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    continue;
                }

                $relativeClass = substr($class, $len);
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        });
    }

    /**
     * 从 .env 文件加载环境变量
     */
    public static function loadEnv() {
        $envFile = J_PATH . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    if (function_exists('putenv')) {
                        putenv(sprintf('%s=%s', $name, $value));
                    }
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

    /**
     * 加载应用配置文件
     */
    public static function loadConfig() {
        $configFile = J_APP . '/config/config.php';
        if (file_exists($configFile)) {
            self::$config = include $configFile;
        }

        $dbConfigFile = J_PATH . '/config/database.php';
        if (file_exists($dbConfigFile)) {
            self::$config['database'] = include $dbConfigFile;
        }

        $sessionConfigFile = J_APP . '/config/session.php';
        if (file_exists($sessionConfigFile)) {
            self::$config['session'] = include $sessionConfigFile;
        }
    }

    /**
     * 初始化依赖注入容器并绑定核心服务
     */
    public static function initContainer() {
        self::$container = new Container();
        self::$container->instance('config', self::$config);
        self::$container->instance('container', self::$container);
        
        self::$providerManager = new ServiceProviderManager(self::$container);
        
        // 绑定日志服务
        self::$container->singleton('logger', function($c) {
            $logLevel = $c->make('config')['app']['log_level'] ?? Logger::DEBUG;
            return new Logger($logLevel);
        });
        
        // 绑定视图服务
        self::$container->singleton('view', function($c) {
            return new View();
        });
        
        // 绑定 Session 服务
        self::$container->singleton('session', function($c) {
            $config = $c->make('config');
            $driver = $config['session']['driver'] ?? 'file';
            return new Session($driver);
        });

        // 绑定 Request 服务
        self::$container->singleton('request', function($c) {
            return new Request();
        });

        // 绑定 Response 响应工厂
        self::$container->bind('response', function($c, $params = []) {
            return new Response(...$params);
        });
    }

    /**
     * 注册配置中的服务提供者
     */
    protected static function registerProviders() {
        $providers = self::$config['providers'] ?? [];
        foreach ($providers as $provider) {
            self::$providerManager->registerProvider($provider);
        }
    }

    /**
     * 执行所有已注册服务提供者的 boot 方法
     */
    protected static function bootProviders() {
        self::$providerManager->bootProviders();
    }

    /**
     * 初始化全局日志实例
     */
    public static function initLogger() {
        self::$logger = self::$container->make('logger');
    }

    /**
     * 初始化并启动 Session
     */
    public static function initSession() {
        self::$session = self::$container->make('session');
        self::$session->start();
    }

    /**
     * 设置数据库工厂配置
     */
    public static function initDatabase() {
        $dbConfig = self::$config['database'] ?? [];
        if (isset($dbConfig['connections'])) {
            $default = $dbConfig['default'] ?? 'mysql';
            DBFactory::setConfig($dbConfig['connections'], $default);
            
            // 绑定核心 db 服务到容器，以便迁移工具和其它组件使用
            self::$container->singleton('db', function() use ($dbConfig) {
                $default = $dbConfig['default'] ?? 'mysql';
                return DBFactory::getConnection($default);
            });
        }
    }

    /**
     * 设置 Redis 门面配置
     */
    public static function initRedis() {
        if (isset(self::$config['database']['redis'])) {
            \JThink\Facade\Redis::setConfig(self::$config['database']['redis']);
        }
    }

    /**
     * 获取视图引擎实例
     */
    public static function initView() {
        self::$view = self::$container->make('view');
    }

    /**
     * 解析当前 HTTP 请求参数（兼容模式）
     */
    public static function parseRequest() {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = str_replace('/public/', '/', $url);
        $url = '/' . trim($url, '/');

        self::$request['url'] = $url;
        self::$request['method'] = $_SERVER['REQUEST_METHOD'];
        self::$request['controller'] = 'index';
        self::$request['action'] = 'index';
        self::$request['params'] = [];

        if ($url) {
            $array = explode('/', $url);
            self::$request['controller'] = $array[0];
            self::$request['action'] = isset($array[1]) ? $array[1] : 'index';
            self::$request['params'] = array_slice($array, 2);
        }

        self::$request['get'] = $_GET;
        self::$request['post'] = $_POST;
        self::$request['server'] = $_SERVER;
        self::$request['files'] = $_FILES;
    }

    /**
     * 初始化并加载路由配置
     */
    public static function loadRouter() {
        $cacheEnabled = self::$config['app']['route_cache'] ?? false;
        self::$router = new Router($cacheEnabled);
        
        $routeFile = J_APP . '/config/route.php';
        if (file_exists($routeFile)) {
            $router = self::$router; // 显式定义 $router 变量给 route.php 使用
            require $routeFile;
        }
    }

    /**
     * 执行路由分发，调用控制器逻辑
     */
    public static function dispatch() {
        try {
            $result = self::$router->dispatch(
                self::$request['method'],
                self::$request['url']
            );

            if ($result === false) {
                self::error(404, 'Page Not Found');
            }
        } catch (\Throwable $e) {
            self::handleFatalError($e); // 使用统一异常处理逻辑
        }
    }


    /**
     * 处理致命错误或未捕获异常，记录日志并由 error() 统一渲染
     * @param \Throwable $e
     */
    protected static function handleFatalError(\Throwable $e) {
        if (self::$logger) {
            self::$logger->critical($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $errMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $errFile = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $errTrace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        
        $message = "<h3>Message: {$errMsg}</h3>";
        $message .= "<p><strong>File:</strong> {$errFile} (Line: {$e->getLine()})</p>";
        $message .= "<hr><p><strong>Stack Trace:</strong></p>";
        $message .= "<pre><code>{$errTrace}</code></pre>";

        self::error(500, $message);
    }

    /**
     * 渲染 HTTP 错误页面
     * @param int $code 错误状态码
     * @param string $message 错误信息
     */
    public static function error($code, $message) {
        http_response_code($code);
        
        $debug = self::$config['app']['debug'] ?? false;
        if (!$debug) {
            $message = 'An internal server error occurred.';
        }

        // 引入全局 CSS
        $cssUrl = '/css/jthink.css';
        $phpVersion = PHP_VERSION;

        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Error {$code} - JThinkPHP</title>
    <link rel="stylesheet" href="{$cssUrl}">
</head>
<body class="j-bg">
    <div class="j-container">
        <div class="j-header">
            <h1>Framework Exception (Status {$code})</h1>
            <p>Powered by JThinkPHP (PHP v{$phpVersion})</p>
        </div>
        
        <div class="j-main">
            <div class="j-card">
                {$message}
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        echo $html;
        exit();
    }

    /**
     * 生成完整的 URL
     * @param string $path
     * @return string
     */
    public static function url($path = '') {
        $baseUrl = self::$config['app']['base_url'] ?? '';
        return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * 获取 DI 容器
     */
    public static function container() {
        return self::$container;
    }

    /**
     * 获取日志实例
     */
    public static function logger() {
        return self::$logger;
    }

    /**
     * 获取视图引擎
     */
    public static function view() {
        return self::$view;
    }

    /**
     * 获取 Session 管理器
     */
    public static function session() {
        return self::$session;
    }

    /**
     * 获取服务提供者管理器
     */
    public static function providerManager() {
        return self::$providerManager;
    }
}