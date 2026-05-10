<?php

namespace JThink\Middleware;

use JThink\Core\Http\Middleware as BaseMiddleware;
use JThink\Core\Support\JWT;

/**
 * API 身份验证中间件
 * 
 * 职责：拦截并验证请求头中的 Authorization 令牌（Bearer Token）。
 * 如果 Token 无效或缺失，直接返回 401 JSON 响应。
 */
class ApiAuth extends BaseMiddleware {
    /**
     * 执行中间件逻辑
     */
    public function handle($request = null, $next = null) {
        $token = $this->getBearerToken();

        if (!$token || !JWT::verify($token)) {
            $this->json([
                'code' => 401,
                'msg' => 'Unauthorized: Invalid or missing token',
                'data' => null
            ], 401);
            return false; // 终止后续路由执行
        }

        return true; // 继续执行
    }

    /**
     * 从请求头中提取 Bearer Token
     */
    protected function getBearerToken() {
        $headers = $this->getHeaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * 获取所有 HTTP 请求头
     */
    protected function getHeaders() {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
