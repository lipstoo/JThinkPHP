<?php

namespace JThink\Core;

abstract class Middleware {
    protected $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    abstract public function handle($request = null, $next = null);

    protected function redirect($url, $status = 302) {
        header("Location: {$url}", true, $status);
        exit();
    }

    protected function abort($code = 404, $message = '') {
        http_response_code($code);
        die($message ?: "Error {$code}");
    }

    protected function json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit();
    }
}
