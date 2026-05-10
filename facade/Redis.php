<?php

namespace JThink\Facade;

use JThink\Core\RedisClient;

class Redis {
    protected static $client = null;
    protected static $config = [];

    public static function setConfig($config) {
        self::$config = $config;
    }

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

    public static function disconnect() {
        if (self::$client !== null) {
            self::$client->disconnect();
            self::$client = null;
        }
    }

    public static function getRawClient() {
        return self::getClient()->getClient();
    }
}
