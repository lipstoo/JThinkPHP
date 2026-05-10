<?php

namespace JThink\Core;

class Request {
    protected $get;
    protected $post;
    protected $server;
    protected $files;
    protected $cookies;
    protected $headers;

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->headers = $this->extractHeaders();
    }

    public function get($key = null, $default = null) {
        if ($key === null) return $this->get;
        return $this->get[$key] ?? $default;
    }

    public function post($key = null, $default = null) {
        if ($key === null) return $this->post;
        return $this->post[$key] ?? $default;
    }

    public function input($key, $default = null) {
        return $this->post($key) ?? $this->get($key) ?? $default;
    }

    public function all() {
        return array_merge($this->get, $this->post);
    }

    public function method() {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function isMethod($method) {
        return strtoupper($this->method()) === strtoupper($method);
    }

    public function header($key, $default = null) {
        $key = str_replace('_', '-', strtolower($key));
        return $this->headers[$key] ?? $default;
    }

    public function url() {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function fullUrl() {
        $protocol = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? "https" : "http";
        return "{$protocol}://{$this->server['HTTP_HOST']}{$this->url()}";
    }

    public function isAjax() {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    protected function extractHeaders() {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headerKey = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }
}
