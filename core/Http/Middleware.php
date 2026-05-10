<?php

namespace JThink\Core\Http;

use JThink\Core\Support\Container;

/**
 * 中间件抽象基类
 * 
 * 职责：定义中间件的标准结构，提供重定向、中止请求及 JSON 响应等工具方法。
 */
abstract class Middleware {
    /** @var Container 容器实例 */
    protected $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * 处理请求的逻辑核心
     */
    abstract public function handle($request = null, $next = null);

    /**
     * 快捷重定向
     */
    protected function redirect($url, $status = 302) {
        header("Location: {$url}", true, $status);
        exit();
    }

    /**
     * 中止请求并返回状态码
     */
    protected function abort($code = 404, $message = '') {
        http_response_code($code);
        die($message ?: "Error {$code}");
    }

    /**
     * 快捷返回 JSON
     */
    protected function json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }
}
