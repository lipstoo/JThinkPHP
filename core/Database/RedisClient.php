<?php

namespace JThink\Core\Database;

use Redis;
use RedisException;

class RedisClient {
    protected $client = null;
    protected $config = [];
    protected $connected = false;

    public function __construct($config) {
        $this->config = $config;
    }

    public function connect() {
        try {
            $this->client = new Redis();
            
            if (isset($this->config['persistent']) && $this->config['persistent']) {
                $this->client->pconnect(
                    $this->config['host'] ?? '127.0.0.1',
                    $this->config['port'] ?? 6379,
                    $this->config['timeout'] ?? 0,
                    $this->config['persistent_id'] ?? 'jthink_redis'
                );
            } else {
                $this->client->connect(
                    $this->config['host'] ?? '127.0.0.1',
                    $this->config['port'] ?? 6379,
                    $this->config['timeout'] ?? 0
                );
            }

            if (isset($this->config['password']) && !empty($this->config['password'])) {
                $this->client->auth($this->config['password']);
            }

            if (isset($this->config['database']) && is_numeric($this->config['database'])) {
                $this->client->select($this->config['database']);
            }

            if (isset($this->config['prefix']) && !empty($this->config['prefix'])) {
                $this->client->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
            }

            $this->connected = true;
            return true;
        } catch (RedisException $e) {
            throw new \Exception("Redis连接失败: " . $e->getMessage());
        }
    }

    public function disconnect() {
        if ($this->client) {
            $this->client->close();
            $this->client = null;
        }
        $this->connected = false;
    }

    public function isConnected() {
        return $this->connected;
    }

    // 字符串操作
    public function set($key, $value, $expire = null) {
        if ($expire !== null) {
            return $this->client->set($key, $value, $expire);
        }
        return $this->client->set($key, $value);
    }

    public function get($key) {
        return $this->client->get($key);
    }

    public function del($key) {
        return $this->client->del($key);
    }

    public function exists($key) {
        return $this->client->exists($key);
    }

    public function expire($key, $seconds) {
        return $this->client->expire($key, $seconds);
    }

    public function ttl($key) {
        return $this->client->ttl($key);
    }

    public function incr($key) {
        return $this->client->incr($key);
    }

    public function incrBy($key, $value) {
        return $this->client->incrBy($key, $value);
    }

    public function decr($key) {
        return $this->client->decr($key);
    }

    public function decrBy($key, $value) {
        return $this->client->decrBy($key, $value);
    }

    // 哈希操作
    public function hSet($key, $field, $value) {
        return $this->client->hSet($key, $field, $value);
    }

    public function hGet($key, $field) {
        return $this->client->hGet($key, $field);
    }

    public function hGetAll($key) {
        return $this->client->hGetAll($key);
    }

    public function hExists($key, $field) {
        return $this->client->hExists($key, $field);
    }

    public function hDel($key, $field) {
        return $this->client->hDel($key, $field);
    }

    public function hLen($key) {
        return $this->client->hLen($key);
    }

    public function hKeys($key) {
        return $this->client->hKeys($key);
    }

    public function hVals($key) {
        return $this->client->hVals($key);
    }

    public function hSetNx($key, $field, $value) {
        return $this->client->hSetNx($key, $field, $value);
    }

    // 列表操作
    public function lPush($key, $value) {
        return $this->client->lPush($key, $value);
    }

    public function rPush($key, $value) {
        return $this->client->rPush($key, $value);
    }

    public function lPop($key) {
        return $this->client->lPop($key);
    }

    public function rPop($key) {
        return $this->client->rPop($key);
    }

    public function lLen($key) {
        return $this->client->lLen($key);
    }

    public function lIndex($key, $index) {
        return $this->client->lIndex($key, $index);
    }

    public function lRange($key, $start, $end) {
        return $this->client->lRange($key, $start, $end);
    }

    // 集合操作
    public function sAdd($key, $value) {
        return $this->client->sAdd($key, $value);
    }

    public function sMembers($key) {
        return $this->client->sMembers($key);
    }

    public function sIsMember($key, $value) {
        return $this->client->sIsMember($key, $value);
    }

    public function sRem($key, $value) {
        return $this->client->sRem($key, $value);
    }

    public function sCard($key) {
        return $this->client->sCard($key);
    }

    // 有序集合操作
    public function zAdd($key, $score, $value) {
        return $this->client->zAdd($key, $score, $value);
    }

    public function zRange($key, $start, $end, $withScores = false) {
        return $this->client->zRange($key, $start, $end, $withScores);
    }

    public function zRevRange($key, $start, $end, $withScores = false) {
        return $this->client->zRevRange($key, $start, $end, $withScores);
    }

    public function zScore($key, $value) {
        return $this->client->zScore($key, $value);
    }

    public function zRank($key, $value) {
        return $this->client->zRank($key, $value);
    }

    public function zRem($key, $value) {
        return $this->client->zRem($key, $value);
    }

    public function zCard($key) {
        return $this->client->zCard($key);
    }

    // 发布订阅
    public function publish($channel, $message) {
        return $this->client->publish($channel, $message);
    }

    public function subscribe($channels, $callback) {
        return $this->client->subscribe($channels, $callback);
    }

    // 事务
    public function multi() {
        return $this->client->multi();
    }

    public function exec() {
        return $this->client->exec();
    }

    public function discard() {
        return $this->client->discard();
    }

    // Lua脚本
    public function eval($script, $keys = [], $args = []) {
        return $this->client->eval($script, $keys, $args);
    }

    // 键操作
    public function keys($pattern) {
        return $this->client->keys($pattern);
    }

    public function type($key) {
        return $this->client->type($key);
    }

    public function rename($key, $newKey) {
        return $this->client->rename($key, $newKey);
    }

    public function move($key, $db) {
        return $this->client->move($key, $db);
    }

    // 服务器操作
    public function ping() {
        return $this->client->ping();
    }

    public function flushDB() {
        return $this->client->flushDB();
    }

    public function flushAll() {
        return $this->client->flushAll();
    }

    public function info($section = null) {
        return $this->client->info($section);
    }

    public function configGet($pattern) {
        return $this->client->configGet($pattern);
    }

    public function configSet($parameter, $value) {
        return $this->client->configSet($parameter, $value);
    }

    // 流操作 (Redis 7.x)
    public function xAdd($key, $id, $fields) {
        return $this->client->xAdd($key, $id, $fields);
    }

    public function xRead($streams, $count = null, $block = null) {
        return $this->client->xRead($streams, $count, $block);
    }

    public function xGroup($command, $key, $groupname, $id = null, $create = null) {
        return $this->client->xGroup($command, $key, $groupname, $id, $create);
    }

    public function xReadGroup($group, $consumer, $streams, $count = null, $block = null, $noAck = null) {
        return $this->client->xReadGroup($group, $consumer, $streams, $count, $block, $noAck);
    }

    // 布隆过滤器 (Redis 7.x)
    public function bfAdd($key, $value) {
        return $this->client->bfAdd($key, $value);
    }

    public function bfExists($key, $value) {
        return $this->client->bfExists($key, $value);
    }

    public function bfReserve($key, $errorRate, $capacity) {
        return $this->client->bfReserve($key, $errorRate, $capacity);
    }

    // JSON操作 (RedisJSON)
    public function jsonSet($key, $path, $value) {
        if (method_exists($this->client, 'jsonSet')) {
            return $this->client->jsonSet($key, $path, $value);
        }
        throw new \Exception("RedisJSON模块未安装");
    }

    public function jsonGet($key, $path = '.') {
        if (method_exists($this->client, 'jsonGet')) {
            return $this->client->jsonGet($key, $path);
        }
        throw new \Exception("RedisJSON模块未安装");
    }

    public function jsonDel($key, $path) {
        if (method_exists($this->client, 'jsonDel')) {
            return $this->client->jsonDel($key, $path);
        }
        throw new \Exception("RedisJSON模块未安装");
    }

    // 时间序列 (RedisTimeSeries)
    public function tsCreate($key, $options = []) {
        if (method_exists($this->client, 'tsCreate')) {
            return $this->client->tsCreate($key, $options);
        }
        throw new \Exception("RedisTimeSeries模块未安装");
    }

    public function tsAdd($key, $timestamp, $value, $options = []) {
        if (method_exists($this->client, 'tsAdd')) {
            return $this->client->tsAdd($key, $timestamp, $value, $options);
        }
        throw new \Exception("RedisTimeSeries模块未安装");
    }

    public function tsRange($key, $from, $to, $options = []) {
        if (method_exists($this->client, 'tsRange')) {
            return $this->client->tsRange($key, $from, $to, $options);
        }
        throw new \Exception("RedisTimeSeries模块未安装");
    }

    /**
     * 魔术方法：将调用代理到原生 Redis 客户端
     */
    public function __call($name, $arguments) {
        if ($this->client && method_exists($this->client, $name)) {
            return call_user_func_array([$this->client, $name], $arguments);
        }
        throw new \Exception("Redis 方法 {$name} 不存在或客户端未连接");
    }

    // 返回原生客户端
    public function getClient() {
        return $this->client;
    }
}
