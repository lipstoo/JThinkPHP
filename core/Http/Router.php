<?php

namespace JThink\Core\Http;

/**
 * 路由管理类
 * 
 * 职责：负责路由规则的注册、匹配解析、中间件执行以及请求分发到控制器的逻辑。
 * 支持 RESTful 路由、路由分组、参数匹配以及路由缓存。
 */
class Router {
    /** @var array 已注册的所有路由规则 */
    protected $routes = [];
    
    /** @var array 全局中间件列表 */
    protected $middleware = [];
    
    /** @var array 当前分组下的中间件 */
    protected $groupMiddleware = [];
    
    /** @var string 当前路由组的前缀 */
    protected $prefix = '';
    
    /** @var bool 是否启用路由解析缓存 */
    protected $cacheEnabled = false;
    
    /** @var string 路由缓存存储路径 */
    protected $cachePath = null;

    /** @var \JThink\Core\Support\Container 容器实例 */
    protected $container = null;

    /**
     * 构造函数
     * @param bool $cacheEnabled 是否开启路由缓存以提升性能
     */
    public function __construct($cacheEnabled = false) {
        $this->cacheEnabled = $cacheEnabled;
        $this->cachePath = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(__DIR__) . '/storage/cache';
    }

    /**
     * 注册 GET 路由
     */
    public function get($uri, $action, $middleware = []) {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    /**
     * 注册 POST 路由
     */
    public function post($uri, $action, $middleware = []) {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    /**
     * 注册 PUT 路由
     */
    public function put($uri, $action, $middleware = []) {
        $this->addRoute('PUT', $uri, $action, $middleware);
    }

    /**
     * 注册 DELETE 路由
     */
    public function delete($uri, $action, $middleware = []) {
        $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    /**
     * 注册 PATCH 路由
     */
    public function patch($uri, $action, $middleware = []) {
        $this->addRoute('PATCH', $uri, $action, $middleware);
    }

    /**
     * 注册 OPTIONS 路由
     */
    public function options($uri, $action, $middleware = []) {
        $this->addRoute('OPTIONS', $uri, $action, $middleware);
    }

    /**
     * 注册匹配所有 HTTP 方法的路由
     */
    public function any($uri, $action, $middleware = []) {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'] as $method) {
            $this->addRoute($method, $uri, $action, $middleware);
        }
    }

    /**
     * 注册 RESTful 资源路由
     * 自动生成 index, create, store, show, edit, update, destroy 等方法映射
     */
    public function resource($uri, $controller, $middleware = []) {
        $this->get($uri, [$controller, 'index'], $middleware);
        $this->get($uri . '/create', [$controller, 'create'], $middleware);
        $this->post($uri, [$controller, 'store'], $middleware);
        $this->get($uri . '/{id}', [$controller, 'show'], $middleware);
        $this->get($uri . '/{id}/edit', [$controller, 'edit'], $middleware);
        $this->put($uri . '/{id}', [$controller, 'update'], $middleware);
        $this->delete($uri . '/{id}', [$controller, 'destroy'], $middleware);
    }

    /**
     * 添加路由到内部存储列表
     */
    protected function addRoute($method, $uri, $action, $middleware) {
        $uri = '/' . trim($uri, '/');
        $fullUri = $this->prefix ? $this->prefix . $uri : $uri;
        $fullUri = '/' . trim($fullUri, '/');
        
        $this->routes[] = [
            'method' => $method,
            'uri' => $fullUri,
            'action' => $action,
            'middleware' => array_merge($this->groupMiddleware, $middleware)
        ];
    }

    /**
     * 路由分组
     * @param string $prefix 分组前缀
     * @param callable $callback 分组内的路由定义闭包
     */
    public function group($prefix, $callback) {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;
        
        $this->prefix = $previousPrefix . $prefix;
        
        call_user_func($callback, $this);
        
        $this->prefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
        
        return $this;
    }

    /**
     * 为后续注册的路由添加中间件（支持链式调用）
     */
    public function middleware($middleware) {
        if (is_array($middleware)) {
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        } else {
            $this->groupMiddleware[] = $middleware;
        }
        return $this;
    }

    /**
     * 核心调度：根据请求方法和 URI 匹配并执行路由
     * 
     * @param string $requestMethod 请求方法 (GET, POST 等)
     * @param string $requestUri 请求路径
     * @return mixed 执行结果
     */
    public function dispatch($requestMethod, $requestUri) {
        $requestUri = '/' . trim($requestUri, '/');
        
        // 尝试从缓存加载已解析的路由（生产环境优化）
        if ($this->cacheEnabled) {
            $cacheKey = md5($requestMethod . ':' . $requestUri);
            $cacheFile = $this->cachePath . '/route_' . $cacheKey . '.php';
            
            if (file_exists($cacheFile)) {
                $cached = include $cacheFile;
                if ($cached) {
                    return $this->executeAction($cached['action'], $cached['params']);
                }
            }
        }
        
        // 遍历所有路由规则进行正则匹配
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod && $route['method'] !== 'ANY') {
                continue;
            }

            $pattern = $this->compileRoute($route['uri']);
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                $params = $this->extractParams($route['uri'], $matches);
                
                // 执行路由中间件栈
                if (!$this->runMiddleware($route['middleware'])) {
                    return false;
                }
                
                // 写入解析缓存
                if ($this->cacheEnabled) {
                    $this->saveToCache($cacheFile, [
                        'action' => $route['action'],
                        'params' => $params
                    ]);
                }
                
                return $this->executeAction($route['action'], $params);
            }
        }
        
        return false;
    }

    /**
     * 检查路由是否已注册（用于测试）
     */
    public function hasRoute($uri, $method = 'GET') {
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === strtoupper($method)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 将路由定义中的 {param} 转换为正则表达式
     */
    protected function compileRoute($uri) {
        $uri = preg_replace('/\{(\w+)\}/', '([^/]+)', $uri);
        return '#^' . $uri . '$#';
    }

    /**
     * 提取路由中的命名参数
     */
    protected function extractParams($uri, $matches) {
        $params = [];
        preg_match_all('/\{(\w+)\}/', $uri, $paramNames);
        
        foreach ($paramNames[1] as $index => $name) {
            $params[$name] = $matches[$index] ?? null;
        }
        
        return $params;
    }

    /**
     * 递归/顺序执行中间件列表
     */
    protected function runMiddleware($middlewareList) {
        foreach ($middlewareList as $middleware) {
            if (is_string($middleware)) {
                $middlewareClass = '\\JThink\\Middleware\\' . $middleware;
                $container = \JThink\Core\Foundation\JThink::container();
                
                if (class_exists($middlewareClass)) {
                    $middleware = $container ? $container->make($middlewareClass) : new $middlewareClass();
                } elseif (class_exists($middleware)) {
                    $middleware = $container ? $container->make($middleware) : new $middleware();
                } else {
                    continue;
                }
            }
            
            if (method_exists($middleware, 'handle')) {
                $result = $middleware->handle();
                if ($result === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 执行控制器动作或匿名函数闭包
     */
    protected function executeAction($action, $params) {
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }
        
        if (is_string($action) && strpos($action, '@') !== false) {
            $action = explode('@', $action);
        }

        if (is_array($action)) {
            list($controller, $method) = $action;
            
            // 自动补全 App\Controller 命名空间
            if (strpos($controller, '\\') === false) {
                $controllerClass = '\\App\\Controller\\' . ucfirst($controller) . 'Controller';
            } else {
                $controllerClass = $controller;
            }
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller not found: {$controllerClass}");
            }
            
            $instance = new $controllerClass();
            
            if (!method_exists($instance, $method)) {
                throw new \Exception("Method not found: {$method}");
            }
            
            return call_user_func_array([$instance, $method], [$params]);
        }
        
        throw new \Exception("Invalid action format: " . print_r($action, true));
    }

    /**
     * 保存路由解析结果到文件缓存
     */
    protected function saveToCache($cacheFile, $data) {
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
        
        $content = '<?php return ' . var_export($data, true) . ';';
        file_put_contents($cacheFile, $content);
    }

    /**
     * 清理所有路由缓存文件
     */
    public function clearCache() {
        foreach (glob($this->cachePath . '/route_*.php') as $file) {
            unlink($file);
        }
    }

    /**
     * 获取所有已定义的路由（用于 CLI route:list）
     */
    public function getRoutes() {
        return $this->routes;
    }
}
?>