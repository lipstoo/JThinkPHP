<?php

if (!function_exists('env')) {
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
    function dd($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    function dump($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '') {
        return BASE_PATH . ($path ? '/' . $path : '');
    }
}

if (!function_exists('app_path')) {
    function app_path($path = '') {
        return J_APP . ($path ? '/' . $path : '');
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '') {
        return J_PUBLIC . ($path ? '/' . $path : '');
    }
}

if (!function_exists('core_path')) {
    function core_path($path = '') {
        return J_CORE . ($path ? '/' . $path : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '') {
        return BASE_PATH . '/storage' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '') {
        return BASE_PATH . '/config' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('url')) {
    function url($path = '') {
        $config = JThink\Core\JThink::$config;
        $baseUrl = $config['base_url'] ?? '';
        return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('asset')) {
    function asset($path) {
        return url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302) {
        header("Location: {$url}", true, $statusCode);
        exit();
    }
}

if (!function_exists('abort')) {
    function abort($code = 404, $message = '') {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit();
    }
}

if (!function_exists('view')) {
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
    function json_response($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        $session = JThink\Core\JThink::session();
        if (!$session->has('_csrf_token')) {
            $session->set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return $session->get('_csrf_token');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('str_random')) {
    function str_random($length = 16) {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length)), 0, $length);
    }
}

if (!function_exists('now')) {
    function now() {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('today')) {
    function today() {
        return date('Y-m-d');
    }
}

if (!function_exists('validate')) {
    function validate($data, $rules, $messages = []) {
        return JThink\Core\Validator::make($data, $rules, $messages);
    }
}

if (!function_exists('jwt')) {
    function jwt() {
        return JThink\Core\JWT::class;
    }
}

if (!function_exists('uploader')) {
    function uploader($config = []) {
        return new JThink\Core\Uploader($config);
    }
}

if (!function_exists('mailer')) {
    function mailer($config = []) {
        return new JThink\Core\Mailer($config);
    }
}

if (!function_exists('queue')) {
    function queue() {
        $config = config('queue') ?? [];
        $driver = $config['driver'] ?? 'sync';
        return new JThink\Core\Queue($driver, $config);
    }
}

if (!function_exists('send_mail')) {
    function send_mail($to, $subject, $body, $isHtml = true) {
        return JThink\Core\Mailer::sendSimple($to, $subject, $body, $isHtml);
    }
}

if (!function_exists('cache')) {
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
    function logger($level = 'info', $message = null, $context = []) {
        $logger = JThink\Core\JThink::logger();
        
        if ($message === null) {
            return $logger;
        }
        
        return $logger->log($level, $message, $context);
    }
}
?>