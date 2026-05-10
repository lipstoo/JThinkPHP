<?php

namespace JThink\Middleware;

use JThink\Core\Http\Middleware as BaseMiddleware;
use JThink\Core\Foundation\JThink;

/**
 * CSRF 防护中间件
 * 
 * 职责：拦截非幂等 HTTP 请求（POST/PUT 等），校验 CSRF 令牌。
 */
class CsrfMiddleware extends BaseMiddleware {
    protected $except = [
        '/api/*',
        '*.json'
    ];

    public function handle() {
        $request = JThink::$request;
        
        if ($this->isExcepted($request['url'])) {
            return true;
        }

        if (in_array($request['method'], ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $request['post']['_token'] ?? $request['server']['HTTP_X_CSRF_TOKEN'] ?? null;
            
            if (!$token || !$this->validateToken($token)) {
                $this->json(['error' => 'CSRF token mismatch'], 419);
                return false;
            }
        }

        return true;
    }

    protected function validateToken($token) {
        $session = JThink::session();
        $storedToken = $session->get('_csrf_token');
        
        if (!$storedToken) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    protected function isExcepted($url) {
        foreach ($this->except as $pattern) {
            if (fnmatch($pattern, $url)) {
                return true;
            }
        }
        return false;
    }
}