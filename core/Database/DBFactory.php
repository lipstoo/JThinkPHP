<?php

namespace JThink\Core\Database;

/**
 * 数据库连接工厂类
 * 
 * 职责：负责根据配置实例化不同的数据库适配器，并维护连接池实现单例连接。
 */
class DBFactory {
    /** @var array 活跃的连接池 */
    protected static $connections = [];
    
    /** @var string 默认连接名 */
    protected static $defaultConnection = 'mysql';
    
    /** @var array 数据库配置信息 */
    protected static $config = [];

    /**
     * 设置全局数据库配置
     */
    public static function setConfig($config, $default = 'mysql') {
        self::$config = $config;
        self::$defaultConnection = $default;
    }

    /**
     * 获取命名的数据库连接实例
     * @param string $name 连接名
     */
    public static function getConnection($name = null) {
        $name = $name ?: self::$defaultConnection;
        
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

    /**
     * 根据驱动名获取适配器全类名
     */
    protected static function getAdapterClass($driver) {
        $drivers = [
            'mysql'      => '\JThink\Core\Database\Adapters\MySQLAdapter',
            'pgsql'      => '\JThink\Core\Database\Adapters\PostgreSQLAdapter',
            'postgres'   => '\JThink\Core\Database\Adapters\PostgreSQLAdapter',
            'postgresql' => '\JThink\Core\Database\Adapters\PostgreSQLAdapter',
            'sqlite'     => '\JThink\Core\Database\Adapters\SQLiteAdapter',
            'sqlite3'    => '\JThink\Core\Database\Adapters\SQLiteAdapter',
            'sqlserver'  => '\JThink\Core\Database\Adapters\SQLServerAdapter',
            'mssql'      => '\JThink\Core\Database\Adapters\SQLServerAdapter'
        ];

        return $drivers[strtolower($driver)] ?? null;
    }

    /**
     * 关闭指定连接
     */
    public static function closeConnection($name = 'default') {
        if (isset(self::$connections[$name])) {
            self::$connections[$name]->disconnect();
            unset(self::$connections[$name]);
        }
    }

    /**
     * 关闭所有连接
     */
    public static function closeAllConnections() {
        foreach (self::$connections as $name => $connection) {
            $connection->disconnect();
        }
        self::$connections = [];
    }

    /**
     * 静态魔术调用，默认调用 default 连接的方法
     */
    public static function __callStatic($method, $args) {
        $connection = self::getConnection();
        return call_user_func_array([$connection, $method], $args);
    }
}
