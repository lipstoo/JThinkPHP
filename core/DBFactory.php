<?php

namespace JThink\Core;

class DBFactory {
    protected static $connections = [];
    protected static $config = [];

    public static function setConfig($config) {
        self::$config = $config;
    }

    public static function getConnection($name = 'default') {
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        if (!isset(self::$config[$name])) {
            throw new \Exception("数据库配置 {$name} 不存在");
        }

        $config = self::$config[$name];
        $driver = $config['driver'] ?? 'mysql';

        $adapterClass = self::getAdapterClass($driver);

        if (!class_exists($adapterClass)) {
            throw new \Exception("不支持的数据库驱动: {$driver}");
        }

        $connection = new $adapterClass($config);
        $connection->connect();

        self::$connections[$name] = $connection;
        return $connection;
    }

    protected static function getAdapterClass($driver) {
        $drivers = [
            'mysql' => '\JThink\Core\DB\MySQLAdapter',
            'pgsql' => '\JThink\Core\DB\PostgreSQLAdapter',
            'postgres' => '\JThink\Core\DB\PostgreSQLAdapter',
            'postgresql' => '\JThink\Core\DB\PostgreSQLAdapter',
            'sqlite' => '\JThink\Core\DB\SQLiteAdapter',
            'sqlite3' => '\JThink\Core\DB\SQLiteAdapter',
            'sqlserver' => '\JThink\Core\DB\SQLServerAdapter',
            'mssql' => '\JThink\Core\DB\SQLServerAdapter'
        ];

        return $drivers[strtolower($driver)] ?? null;
    }

    public static function closeConnection($name = 'default') {
        if (isset(self::$connections[$name])) {
            self::$connections[$name]->disconnect();
            unset(self::$connections[$name]);
        }
    }

    public static function closeAllConnections() {
        foreach (self::$connections as $name => $connection) {
            $connection->disconnect();
        }
        self::$connections = [];
    }

    public static function __callStatic($method, $args) {
        $connection = self::getConnection();
        return call_user_func_array([$connection, $method], $args);
    }
}
