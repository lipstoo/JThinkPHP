<?php

namespace JThink\Core\Http;

use JThink\Core\Foundation\JThink;

class Session {
    protected $started = false;
    protected $driver;
    protected $config;

    public function __construct($driver = 'file') {
        $this->driver = $driver;
        $this->config = JThink::$config['session'] ?? [];
    }

    public function start() {
        if ($this->started) {
            return;
        }

        $this->configureSession();

        switch ($this->driver) {
            case 'redis':
                $this->startRedisSession();
                break;
            default:
                $this->startFileSession();
                break;
        }

        $this->started = true;
    }

    protected function configureSession() {
        $config = $this->config;
        
        ini_set('session.cookie_httponly', $config['cookie_httponly'] ?? true);
        ini_set('session.cookie_secure', $config['cookie_secure'] ?? false);
        ini_set('session.cookie_samesite', $config['cookie_samesite'] ?? 'Lax');
        ini_set('session.use_strict_mode', $config['strict_mode'] ?? true);
        
        session_name($config['name'] ?? 'JThinkSession');
        
        if (isset($config['lifetime'])) {
            ini_set('session.gc_maxlifetime', $config['lifetime']);
            session_set_cookie_params($config['lifetime']);
        }
    }

    protected function startFileSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function startRedisSession() {
        $redisConfig = $this->config['redis'] ?? [];
        
        ini_set('session.save_handler', 'redis');
        $host = $redisConfig['host'] ?? '127.0.0.1';
        $port = $redisConfig['port'] ?? 6379;
        
        $savePath = "tcp://{$host}:{$port}";
        if (!empty($redisConfig['password'])) {
            $savePath .= ",password={$redisConfig['password']}";
        }
        if (isset($redisConfig['database'])) {
            $savePath .= ",database={$redisConfig['database']}";
        }
        
        ini_set('session.save_path', $savePath);
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set($key, $value) {
        $this->start();
        $_SESSION[$key] = $value;
    }

    public function get($key, $default = null) {
        $this->start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public function has($key) {
        $this->start();
        return isset($_SESSION[$key]);
    }

    public function remove($key) {
        $this->start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function flush() {
        $this->start();
        $_SESSION = [];
    }

    public function destroy() {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
            $this->started = false;
        }
    }

    public function regenerate($destroy = true) {
        $this->start();
        session_regenerate_id($destroy);
    }

    public function getId() {
        $this->start();
        return session_id();
    }

    public function setId($id) {
        session_id($id);
    }

    public function setFlash($key, $value) {
        $this->set('_flash.' . $key, $value);
    }

    public function getFlash($key, $default = null) {
        $value = $this->get('_flash.' . $key, $default);
        $this->remove('_flash.' . $key);
        return $value;
    }

    public function all() {
        $this->start();
        return $_SESSION;
    }
}