<?php

namespace App\Controller;

use JThink\Core\Support\JWT;
use JThink\Core\Http\Request;

/**
 * API 示例控制器
 * 
 * 展示了如何生成 Token 以及如何返回标准 JSON 格式数据。
 */
class ApiController {
    /**
     * 用户登录并获取 Token
     * 实际应用中应验证用户名密码
     */
    public function login(Request $request) {
        $username = $request->input('username', 'guest');
        
        // 构建载荷
        $payload = [
            'user_id' => 123,
            'username' => $username,
            'role' => 'admin'
        ];

        // 生成过期时间为 2 小时的 Token
        $token = JWT::createToken($payload, 7200);

        return json_response([
            'code' => 200,
            'msg' => 'Login successful',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 7200
            ]
        ]);
    }

    /**
     * 受保护 of the resource 接口
     */
    public function profile() {
        // 如果能进入此方法，说明中间件已验证通过
        return json_response([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'user_id' => 123,
                'email' => 'admin@example.com',
                'nickname' => 'JThinker'
            ]
        ]);
    /**
     * 业务演示接口
     */
    public function hello() {
        return json_response([
            'code' => 200,
            'msg' => 'Hello JThinkPHP!',
            'time' => date('Y-m-d H:i:s')
        ]);
    }
}
