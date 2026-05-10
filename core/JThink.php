<?php

namespace JThink\Core;

class JThink {
    public static $config = [];
    public static $request = [];
    public static $router = null;
    public static $container = null;
    public static $logger = null;
    public static $view = null;
    public static $session = null;
    protected static $providerManager = null;

    public static function run() {
        try {
            self::defineConstants();
            self::registerAutoloader();
            self::loadEnv();
            self::loadConfig();
            self::initContainer();
            self::registerProviders();
            self::initLogger();
            self::initSession();
            self::initDatabase();
            self::initRedis();
            self::initView();
            self::bootProviders();
            self::parseRequest();
            self::loadRouter();
            self::dispatch();
        } catch (\Exception $e) {
            self::handleFatalError($e);
        }
    }

    protected static function defineConstants() {
        if (!defined('J_PATH')) {
            define('J_PATH', dirname(__DIR__));
        }
        if (!defined('J_CORE')) {
            define('J_CORE', J_PATH . '/core');
        }
        require_once J_CORE . '/functions.php';
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

    protected static function registerAutoloader() {
        spl_autoload_register(function ($class) {
            $prefixes = [
                'JThink\\Core\\' => J_CORE . '/',
                'JThink\\Facade\\' => J_PATH . '/facade/',
                'JThink\\Middleware\\' => J_CORE . '/middleware/',
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

    protected static function loadEnv() {
        $envFile = J_PATH . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

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

    public static function initContainer() {
        self::$container = new Container();
        self::$container->instance('config', self::$config);
        self::$container->instance('container', self::$container);
        
        self::$providerManager = new ServiceProviderManager(self::$container);
        
        self::$container->singleton('logger', function($c) {
            $logLevel = $c->make('config')['app']['log_level'] ?? Logger::DEBUG;
            return new Logger($logLevel);
        });
        
        self::$container->singleton('view', function($c) {
            return new View();
        });
        
        self::$container->singleton('session', function($c) {
            $config = $c->make('config');
            $driver = $config['session']['driver'] ?? 'file';
            return new Session($driver);
        });
    }

    protected static function registerProviders() {
        $providers = self::$config['providers'] ?? [];
        
        foreach ($providers as $provider) {
            self::$providerManager->registerProvider($provider);
        }
    }

    protected static function bootProviders() {
        self::$providerManager->bootProviders();
    }

    public static function initLogger() {
        self::$logger = self::$container->make('logger');
    }

    public static function initSession() {
        self::$session = self::$container->make('session');
        self::$session->start();
    }

    public static function initDatabase() {
        if (isset(self::$config['database']['connections'])) {
            DBFactory::setConfig(self::$config['database']['connections']);
        }
    }

    public static function initRedis() {
        if (isset(self::$config['database']['redis'])) {
            \JThink\Facade\Redis::setConfig(self::$config['database']['redis']);
        }
    }

    public static function initView() {
        self::$view = self::$container->make('view');
    }

    public static function parseRequest() {
        $url = $_SERVER['REQUEST_URI'];
        $url = str_replace('/public/', '', $url);
        $url = trim($url, '/');

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

    public static function loadRouter() {
        $cacheEnabled = self::$config['app']['route_cache'] ?? false;
        self::$router = new Router($cacheEnabled);
        
        $routeFile = J_APP . '/config/route.php';
        if (file_exists($routeFile)) {
            require $routeFile;
        }
    }

    public static function dispatch() {
        try {
            $result = self::$router->dispatch(
                self::$request['method'],
                self::$request['url']
            );

            if ($result === false) {
                self::error(404, 'Page Not Found');
            }
        } catch (\Exception $e) {
            if (self::$logger) {
                self::$logger->error($e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            self::error(500, 'Internal Server Error');
        }
    }

    public static function error($code, $message) {
        http_response_code($code);
        
        if (self::$config['app']['debug'] ?? false) {
            $html = "<!DOCTYPE html><html><head><title>Error {$code}</title>";
            $html .= "<style>body{font-family:Arial,sans-serif;margin:40px;background:#f5f5f5;}";
            $html .= ".error{background:#fff;padding:30px;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.15);max-width:600px;margin:0 auto;}";
            $html .= "h1{color:#dc3545;font-size:36px;margin:0 0 15px;display:flex;align-items:center;gap:12px;}";
            $html .= "h1::before{content:'✕';background:#dc3545;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:bold;}";
            $html .= "p{color:#666;margin:0;font-size:14px;line-height:1.6;}";
            $html .= ".code{background:#f8f9fa;padding:12px;border-radius:6px;font-family:monospace;font-size:12px;color:#333;margin-top:15px;}";
            $html .= "</style></head><body><div class='error'><h1>Error {$code}</h1><p>{$message}</p></div></body></html>";
            echo $html;
        } else {
            echo "Error {$code}: {$message}";
        }
    }

    protected static function handleFatalError(\Exception $e) {
        if (self::$logger) {
            self::$logger->critical($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        http_response_code(500);
        
        if (self::$config['app']['debug'] ?? false) {
            $html = "<!DOCTYPE html><html><head><title>Fatal Error</title>";
            $html .= "<style>body{font-family:Arial,sans-serif;margin:40px;background:#f5f5f5;}";
            $html .= ".error{background:#fff;padding:30px;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.15);max-width:800px;margin:0 auto;}";
            $html .= "h1{color:#dc3545;font-size:36px;margin:0 0 15px;display:flex;align-items:center;gap:12px;}";
            $html .= "h1::before{content:'✕';background:#dc3545;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:bold;}";
            $html .= "p{color:#666;margin:0;font-size:14px;line-height:1.6;}";
            $html .= ".code{background:#f8f9fa;padding:12px;border-radius:6px;font-family:monospace;font-size:12px;color:#333;margin-top:15px;white-space:pre-wrap;}";
            $html .= ".stack{background:#2d2d2d;color:#ccc;padding:15px;border-radius:6px;font-family:monospace;font-size:11px;margin-top:10px;max-height:300px;overflow-y:auto;}";
            $html .= "</style></head><body><div class='error'>";
            $html .= "<h1>Fatal Error</h1>";
            $html .= "<p><strong>Message:</strong> {$e->getMessage()}</p>";
            $html .= "<div class='code'><strong>File:</strong> {$e->getFile()}:{$e->getLine()}</div>";
            $html .= "<div class='stack'>{$e->getTraceAsString()}</div>";
            $html .= "</div></body></html>";
            echo $html;
        } else {
            echo "Internal Server Error";
        }
        
        exit();
    }

    public static function url($path = '') {
        $baseUrl = self::$config['app']['base_url'] ?? '';
        return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
    }

    public static function container() {
        return self::$container;
    }

    public static function logger() {
        return self::$logger;
    }

    public static function view() {
        return self::$view;
    }

    public static function session() {
        return self::$session;
    }

    public static function providerManager() {
        return self::$providerManager;
    }
}