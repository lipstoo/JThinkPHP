<?php

/**
 * JThink 全局助手函数库
 * 
 * 职责：提供一系列便捷的快捷函数，简化框架核心组件的调用，提升开发效率。
 */

if (!function_exists('env')) {
    /**
     * 获取环境变量值
     * @param string $key 变量名
     * @param mixed $default 默认值
     */
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        if (is_numeric($value)) {
            return $value + 0;
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * 获取配置项
     * @param string $key 键名（支持点分隔符，如 'app.name'）
     * @param mixed $default 默认值
     */
    function config($key = null, $default = null) {
        if ($key === null) {
            return JThink\Core\JThink::$config;
        }
        
        $keys = explode('.', $key);
        $value = JThink\Core\JThink::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

if (!function_exists('dd')) {
    /**
     * 打印并终止程序 (Dump and Die)
     */
    function dd($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * 打印变量内容但不终止
     */
    function dump($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

if (!function_exists('base_path')) {
    /** 获取根目录绝对路径 */
    function base_path($path = '') {
        return BASE_PATH . ($path ? '/' . $path : '');
    }
}

if (!function_exists('app_path')) {
    /** 获取 app 目录绝对路径 */
    function app_path($path = '') {
        return J_APP . ($path ? '/' . $path : '');
    }
}

if (!function_exists('public_path')) {
    /** 获取 public 目录绝对路径 */
    function public_path($path = '') {
        return J_PUBLIC . ($path ? '/' . $path : '');
    }
}

if (!function_exists('core_path')) {
    /** 获取 core 目录绝对路径 */
    function core_path($path = '') {
        return J_CORE . ($path ? '/' . $path : '');
    }
}

if (!function_exists('storage_path')) {
    /** 获取 storage 目录绝对路径 */
    function storage_path($path = '') {
        return BASE_PATH . '/storage' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('config_path')) {
    /** 获取 config 目录绝对路径 */
    function config_path($path = '') {
        return BASE_PATH . '/config' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('url')) {
    /**
     * 生成应用内部 URL
     */
    function url($path = '') {
        $config = JThink\Core\JThink::$config;
        $baseUrl = $config['base_url'] ?? '';
        return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('asset')) {
    /**
     * 生成静态资源 URL
     */
    function asset($path) {
        return url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * 执行重定向
     */
    function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit();
    }
}

if (!function_exists('abort')) {
    /**
     * 终止程序并返回错误状态码
     */
    function abort($code = 404, $message = '') {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit();
    }
}

if (!function_exists('view')) {
    /**
     * 渲染并显示视图模板
     */
    function view($template, $data = []) {
        $viewPath = app_path('views/' . $template . '.php');
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }
        
        extract($data);
        include $viewPath;
    }
}

if (!function_exists('json_response')) {
    /**
     * 返回 JSON 格式响应
     */
    function json_response($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

if (!function_exists('csrf_token')) {
    /**
     * 获取当前会话的 CSRF 令牌
     */
    function csrf_token() {
        $session = JThink\Core\JThink::session();
        if (!$session->has('_csrf_token')) {
            $session->set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return $session->get('_csrf_token');
    }
}

if (!function_exists('csrf_field')) {
    /**
     * 生成 HTML 隐藏的 CSRF 令牌输入框
     */
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('e')) {
    /**
     * HTML 转义安全输出
     */
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('str_random')) {
    /**
     * 生成随机字符串
     */
    function str_random($length = 16) {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length)), 0, $length);
    }
}

if (!function_exists('now')) {
    /** 获取当前日期时间 */
    function now() {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('today')) {
    /** 获取当前日期 */
    function today() {
        return date('Y-m-d');
    }
}

if (!function_exists('validate')) {
    /**
     * 执行数据验证
     */
    function validate($data, $rules, $messages = []) {
        return JThink\Core\Validator::make($data, $rules, $messages);
    }
}

if (!function_exists('jwt')) {
    /** 获取 JWT 工具类 */
    function jwt() {
        return JThink\Core\JWT::class;
    }
}

if (!function_exists('uploader')) {
    /** 获取文件上传实例 */
    function uploader($config = []) {
        return new JThink\Core\Uploader($config);
    }
}

if (!function_exists('mailer')) {
    /** 获取邮件发送实例 */
    function mailer($config = []) {
        return new JThink\Core\Mailer($config);
    }
}

if (!function_exists('queue')) {
    /** 获取队列管理实例 */
    function queue() {
        $config = config('queue') ?? [];
        $driver = $config['driver'] ?? 'sync';
        return new JThink\Core\Queue($driver, $config);
    }
}

if (!function_exists('send_mail')) {
    /** 快捷发送邮件 */
    function send_mail($to, $subject, $body, $isHtml = true) {
        return JThink\Core\Mailer::sendSimple($to, $subject, $body, $isHtml);
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存助手函数：获取、设置或删除缓存
     * @param string $key 键名
     * @param mixed $value 如果为 null 则获取，如果为 false 则删除，否则设置
     * @param int $minutes 过期时间（分钟）
     */
    function cache($key = null, $value = null, $minutes = 0) {
        $cacheDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : base_path('storage/cache');
        
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . '/cache_' . md5($key) . '.php';
        
        if ($key === null) {
            return null;
        }
        
        if ($value === null) {
            if (!file_exists($cacheFile)) {
                return null;
            }
            
            $content = file_get_contents($cacheFile);
            $data = unserialize($content);
            
            if ($data['expire'] > 0 && time() > $data['expire']) {
                unlink($cacheFile);
                return null;
            }
            
            return $data['value'];
        }
        
        if ($value === false) {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
            return true;
        }
        
        $expire = $minutes > 0 ? time() + ($minutes * 60) : 0;
        
        $data = [
            'value' => $value,
            'expire' => $expire,
        ];
        
        file_put_contents($cacheFile, serialize($data));
        
        return $value;
    }
}

if (!function_exists('logger')) {
    /**
     * 日志助手函数
     */
    function logger($level = 'info', $message = null, $context = []) {
        $logger = JThink\Core\JThink::logger();
        
        if ($message === null) {
            return $logger;
        }
        
        return $logger->log($level, $message, $context);
    }
}

if (!function_exists('request')) {
    /**
     * 获取请求实例或请求参数
     */
    function request($key = null, $default = null) {
        $request = JThink\Core\JThink::container()->make('request');
        if ($key === null) return $request;
        return $request->input($key, $default);
    }
}

if (!function_exists('response')) {
    /**
     * 获取响应工厂或创建响应实例
     */
    function response($content = '', $status = 200, $headers = []) {
        return JThink\Core\JThink::container()->make('response', [$content, $status, $headers]);
    }
}