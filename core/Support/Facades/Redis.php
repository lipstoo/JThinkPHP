<?php

namespace JThink\Facade;

use JThink\Core\Database\RedisClient;

/**
 * Redis 静态门面 (Facade)
 * 
 * 职责：为 Redis 操作提供简洁的静态调用接口。
 */
class Redis {
    protected static $client = null;
    protected static $config = [];

    /**
     * 设置配置
     */
    public static function setConfig($config) {
        self::$config = $config;
    }

    /**
     * 获取客户端实例
     */
    protected static function getClient() {
        if (self::$client === null) {
            self::$client = new RedisClient(self::$config);
            self::$client->connect();
        }
        return self::$client;
    }

    public static function __callStatic($method, $args) {
        $client = self::getClient();
        
        if (method_exists($client, $method)) {
            return call_user_func_array([$client, $method], $args);
        }
        
        throw new \Exception("方法 {$method} 不存在");
    }

    /**
     * 断开连接
     */
    public static function disconnect() {
        if (self::$client !== null) {
            self::$client->disconnect();
            self::$client = null;
        }
    }

    /**
     * 获取原生 Redis 客户端
     */
    public static function getRawClient() {
        return self::getClient()->getClient();
    }
}
