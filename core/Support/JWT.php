<?php

namespace JThink\Core\Support;

/**
 * JWT (JSON Web Token) 工具类
 * 
 * 职责：负责无状态身份认证令牌的生成、解码与验证。
 * 常用于 API 接口的权限验证，支持过期时间检查及 Base64Url 编码。
 */
class JWT {
    /** @var array 默认配置 */
    protected static $config = [
        'secret' => 'jthink_secret_key', // 签名密钥（建议在 .env 中修改）
        'algorithm' => 'HS256',          // 签名算法
        'expire' => 3600,                // 默认过期时间（秒）
    ];

    /**
     * 合并自定义配置
     */
    public static function setConfig($config) {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * 将负载数据编码为 JWT 字符串
     * 
     * @param array $payload 数据载荷
     * @param string|null $secret 密钥
     * @return string
     */
    public static function encode($payload, $secret = null) {
        $secret = $secret ?? self::$config['secret'];
        $algorithm = self::$config['algorithm'];
        
        $header = [
            'typ' => 'JWT',
            'alg' => $algorithm,
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // 生成签名
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * 解码 JWT 字符串并验证签名及有效期
     * 
     * @param string $token 令牌
     * @param string|null $secret 密钥
     * @return array 载荷数据
     * @throws \Exception
     */
    public static function decode($token, $secret = null) {
        $secret = $secret ?? self::$config['secret'];
        
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // 验证签名是否匹配
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);

        if (!hash_equals($signature, $expectedSignature)) {
            throw new \Exception('Invalid signature');
        }

        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        // 检查是否过期
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('Token has expired');
        }

        return $payload;
    }

    /**
     * 快速验证 Token 有效性
     */
    public static function verify($token, $secret = null) {
        try {
            self::decode($token, $secret);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取 Token 中的原始载荷数据（不验证签名）
     */
    public static function getPayload($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        return json_decode(self::base64UrlDecode($parts[1]), true);
    }

    /**
     * 创建包含 iat 和 exp 的 Token
     * 
     * @param array $payload 数据
     * @param int|null $expire 有效时长（秒）
     */
    public static function createToken($payload, $expire = null) {
        $expire = $expire ?? self::$config['expire'];
        
        $payload['iat'] = time(); // 签发时间
        $payload['exp'] = time() + $expire; // 过期时间
        
        return self::encode($payload);
    }

    /**
     * 刷新 Token（延长有效期）
     */
    public static function refreshToken($token) {
        $payload = self::decode($token);
        unset($payload['iat'], $payload['exp']);
        return self::createToken($payload);
    }

    /**
     * 符合 JWT 规范的 Base64 编码
     */
    protected static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * 符合 JWT 规范的 Base64 解码
     */
    protected static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}