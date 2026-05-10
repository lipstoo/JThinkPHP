<?php

namespace JThink\Core;

class JWT {
    protected static $config = [
        'secret' => 'jthink_secret_key',
        'algorithm' => 'HS256',
        'expire' => 3600,
    ];

    public static function setConfig($config) {
        self::$config = array_merge(self::$config, $config);
    }

    public static function encode($payload, $secret = null) {
        $secret = $secret ?? self::$config['secret'];
        $algorithm = self::$config['algorithm'];
        
        $header = [
            'typ' => 'JWT',
            'alg' => $algorithm,
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function decode($token, $secret = null) {
        $secret = $secret ?? self::$config['secret'];
        
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);

        if (!hash_equals($signature, $expectedSignature)) {
            throw new \Exception('Invalid signature');
        }

        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('Token has expired');
        }

        return $payload;
    }

    public static function verify($token, $secret = null) {
        try {
            self::decode($token, $secret);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getPayload($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }

        return json_decode(self::base64UrlDecode($parts[1]), true);
    }

    public static function createToken($payload, $expire = null) {
        $expire = $expire ?? self::$config['expire'];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expire;
        
        return self::encode($payload);
    }

    public static function refreshToken($token) {
        $payload = self::decode($token);
        
        unset($payload['iat'], $payload['exp']);
        
        return self::createToken($payload);
    }

    protected static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>