<?php

namespace JThink\Core;

class Router {
    protected $routes = [];
    protected $middleware = [];
    protected $groupMiddleware = [];
    protected $prefix = '';
    protected $cacheEnabled = false;
    protected $cachePath = null;

    public function __construct($cacheEnabled = false) {
        $this->cacheEnabled = $cacheEnabled;
        $this->cachePath = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(__DIR__) . '/storage/cache';
    }

    public function get($uri, $action, $middleware = []) {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post($uri, $action, $middleware = []) {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put($uri, $action, $middleware = []) {
        $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function delete($uri, $action, $middleware = []) {
        $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function patch($uri, $action, $middleware = []) {
        $this->addRoute('PATCH', $uri, $action, $middleware);
    }

    public function options($uri, $action, $middleware = []) {
        $this->addRoute('OPTIONS', $uri, $action, $middleware);
    }

    public function any($uri, $action, $middleware = []) {
        foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'] as $method) {
            $this->addRoute($method, $uri, $action, $middleware);
        }
    }

    public function resource($uri, $controller, $middleware = []) {
        $this->get($uri, [$controller, 'index'], $middleware);
        $this->get($uri . '/create', [$controller, 'create'], $middleware);
        $this->post($uri, [$controller, 'store'], $middleware);
        $this->get($uri . '/{id}', [$controller, 'show'], $middleware);
        $this->get($uri . '/{id}/edit', [$controller, 'edit'], $middleware);
        $this->put($uri . '/{id}', [$controller, 'update'], $middleware);
        $this->delete($uri . '/{id}', [$controller, 'destroy'], $middleware);
    }

    protected function addRoute($method, $uri, $action, $middleware) {
        $fullUri = $this->prefix ? $this->prefix . $uri : $uri;
        
        $this->routes[] = [
            'method' => $method,
            'uri' => $fullUri,
            'action' => $action,
            'middleware' => array_merge($this->groupMiddleware, $middleware)
        ];
    }

    public function group($prefix, $callback) {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->groupMiddleware;
        
        $this->prefix = $previousPrefix . $prefix;
        
        call_user_func($callback, $this);
        
        $this->prefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    public function middleware($middleware) {
        if (is_array($middleware)) {
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        } else {
            $this->groupMiddleware[] = $middleware;
        }
        return $this;
    }

    public function dispatch($requestMethod, $requestUri) {
        $requestUri = trim($requestUri, '/');
        
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
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod && $route['method'] !== 'ANY') {
                continue;
            }

            $pattern = $this->compileRoute($route['uri']);
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                $params = $this->extractParams($route['uri'], $matches);
                
                if (!$this->runMiddleware($route['middleware'])) {
                    return false;
                }
                
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

    protected function compileRoute($uri) {
        $uri = preg_replace('/\{(\w+)\}/', '([^/]+)', $uri);
        return '#^' . $uri . '$#';
    }

    protected function extractParams($uri, $matches) {
        $params = [];
        preg_match_all('/\{(\w+)\}/', $uri, $paramNames);
        
        foreach ($paramNames[1] as $index => $name) {
            $params[$name] = $matches[$index] ?? null;
        }
        
        return $params;
    }

    protected function runMiddleware($middlewareList) {
        foreach ($middlewareList as $middleware) {
            if (is_string($middleware)) {
                $middlewareClass = '\\JThink\\Middleware\\' . $middleware;
                if (class_exists($middlewareClass)) {
                    $middleware = new $middlewareClass();
                } elseif (class_exists($middleware)) {
                    $middleware = new $middleware();
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

    protected function executeAction($action, $params) {
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }
        
        if (is_string($action) && strpos($action, '@') !== false) {
            $action = explode('@', $action);
        }

        if (is_array($action)) {
            list($controller, $method) = $action;
            
            // Handle App\Controller namespace automatically
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

    protected function saveToCache($cacheFile, $data) {
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
        
        $content = '<?php return ' . var_export($data, true) . ';';
        file_put_contents($cacheFile, $content);
    }

    public function clearCache() {
        foreach (glob($this->cachePath . '/route_*.php') as $file) {
            unlink($file);
        }
    }

    public function getRoutes() {
        return $this->routes;
    }
}
?>