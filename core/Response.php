<?php

namespace JThink\Core;

class Response {
    protected $content;
    protected $status;
    protected $headers;

    public function __construct($content = '', $status = 200, $headers = []) {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function header($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }

    public function send() {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $key => $value) {
                header("{$key}: {$value}");
            }
        }
        echo $this->content;
    }

    public static function json($data, $status = 200) {
        return new static(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }

    public static function redirect($url, $status = 302) {
        return new static('', $status, ['Location' => $url]);
    }
}
