<?php

namespace JThink\Core\View;

use JThink\Core\Foundation\JThink;

/**
 * 视图引擎类
 * 
 * 职责：负责模板渲染、变量分配、布局管理及视图缓存。
 */
class View {
    protected $path;
    protected $data = [];
    protected $layout = null;
    protected $sections = [];
    protected $currentSection = null;
    protected $cacheEnabled = false;
    protected $cachePath = null;
    protected $cacheDuration = 3600;

    public function __construct($path = null) {
        $basePath = defined('APP_PATH') ? APP_PATH : (defined('J_APP') ? J_APP : dirname(dirname(__DIR__)));
        $this->path = $path ?: $basePath . '/app/views';
        
        $config = JThink::$config['view'] ?? [];
        $this->cacheEnabled = $config['cache_enabled'] ?? false;
        $this->cachePath = $config['cache_path'] ?? (defined('STORAGE_PATH') ? STORAGE_PATH . '/cache/views' : $basePath . '/storage/cache/views');
        $this->cacheDuration = $config['cache_duration'] ?? 3600;
        
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    public function render($template, $data = []) {
        $this->data = $data;
        $templatePath = $this->path . '/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("View not found: {$templatePath}");
        }

        if ($this->cacheEnabled) {
            $cacheKey = md5($template . serialize($data));
            $cacheFile = $this->cachePath . '/' . $cacheKey . '.php';
            
            if ($this->isCacheValid($cacheFile)) {
                ob_start();
                include $cacheFile;
                return ob_get_clean();
            }
        }

        ob_start();
        extract($this->data);
        
        include $templatePath;
        
        $content = ob_get_clean();

        if ($this->layout) {
            $layoutPath = $this->path . '/layouts/' . $this->layout . '.php';
            if (file_exists($layoutPath)) {
                ob_start();
                $__content = $content;
                include $layoutPath;
                $content = ob_get_clean();
            }
        }

        if ($this->cacheEnabled) {
            file_put_contents($cacheFile, $content);
        }

        return $content;
    }

    protected function isCacheValid($cacheFile) {
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $mtime = filemtime($cacheFile);
        return (time() - $mtime) < $this->cacheDuration;
    }

    public function display($template, $data = []) {
        echo $this->render($template, $data);
    }

    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function section($name) {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection() {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    public function yield($name, $default = '') {
        return isset($this->sections[$name]) ? $this->sections[$name] : $default;
    }

    public function partial($partial, $data = []) {
        $partialPath = $this->path . '/partials/' . $partial . '.php';
        
        if (!file_exists($partialPath)) {
            throw new \Exception("Partial not found: {$partialPath}");
        }

        ob_start();
        $currentData = $this->data ?? [];
        extract(array_merge($currentData, $data));
        include $partialPath;
        return ob_get_clean();
    }

    public function escape($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function url($path = '') {
        $config = JThink::$config;
        $baseUrl = $config['app']['base_url'] ?? '';
        return $baseUrl . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function asset($path) {
        return $this->url('public/' . ltrim($path, '/'));
    }

    public function clearCache() {
        foreach (glob($this->cachePath . '/*.php') as $file) {
            unlink($file);
        }
    }
}