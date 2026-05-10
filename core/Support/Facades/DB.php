<?php

namespace JThink\Facade;

use JThink\Core\Database\DBFactory;

/**
 * 数据库静态门面 (Facade)
 * 
 * 职责：为数据库操作提供简洁的静态调用接口。
 */
class DB {
    public static function __callStatic($method, $args) {
        $connection = DBFactory::getConnection();
        
        if (method_exists($connection, $method)) {
            return call_user_func_array([$connection, $method], $args);
        }
        
        throw new \Exception("方法 {$method} 不存在");
    }

    /**
     * 获取指定名称的数据库连接
     */
    public static function connection($name = 'default') {
        return DBFactory::getConnection($name);
    }

    /**
     * 开始一个查询构造器
     */
    public static function table($table) {
        $connection = DBFactory::getConnection();
        return $connection->table($table);
    }

    public static function query($sql, $params = []) {
        return DBFactory::getConnection()->query($sql, $params);
    }

    public static function fetch($sql, $params = []) {
        return DBFactory::getConnection()->fetch($sql, $params);
    }

    public static function fetchAll($sql, $params = []) {
        return DBFactory::getConnection()->fetchAll($sql, $params);
    }

    public static function execute($sql, $params = []) {
        return DBFactory::getConnection()->execute($sql, $params);
    }

    public static function beginTransaction() {
        return DBFactory::getConnection()->beginTransaction();
    }

    public static function commit() {
        return DBFactory::getConnection()->commit();
    }

    public static function rollback() {
        return DBFactory::getConnection()->rollback();
    }

    public static function insert($table, $data) {
        return DBFactory::getConnection()->insert($table, $data);
    }

    public static function update($table, $data, $where) {
        return DBFactory::getConnection()->update($table, $data, $where);
    }

    public static function delete($table, $where) {
        return DBFactory::getConnection()->delete($table, $where);
    }

    public static function lastInsertId() {
        return DBFactory::getConnection()->lastInsertId();
    }
}
