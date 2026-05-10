<?php

namespace JThink\Core\Database;

/**
 * 数据库抽象基类
 * 
 * 职责：定义所有数据库驱动（MySQL, PostgreSQL, SQL Server 等）必须实现的统一接口。
 * 确保了框架核心逻辑与具体数据库底层的解耦，实现“一次编写，多数据库运行”。
 */
abstract class Database {
    /** @var array 数据库连接配置 */
    protected $config = [];
    
    /** @var mixed 底层 PDO 联接对象 */
    protected $connection = null;
    
    /** @var bool 是否处于事务中 */
    protected $transaction = false;

    /**
     * 初始化驱动配置
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /** 连接到数据库 */
    abstract public function connect();
    
    /** 断开连接 */
    abstract public function disconnect();
    
    /** 执行查询语句 */
    abstract public function query($sql, $params = []);
    
    /** 执行非查询语句 */
    abstract public function execute($sql, $params = []);
    
    /** 获取单行结果 */
    abstract public function fetch($sql, $params = []);
    
    /** 获取全部结果 */
    abstract public function fetchAll($sql, $params = []);
    
    /** 插入数据 */
    abstract public function insert($table, $data);
    
    /** 更新数据 */
    abstract public function update($table, $data, $where);
    
    /** 删除数据 */
    abstract public function delete($table, $where);
    
    /** 获取最后插入的 ID */
    abstract public function lastInsertId();
    
    /** 开启事务 */
    abstract public function beginTransaction();
    
    /** 提交事务 */
    abstract public function commit();
    
    /** 回滚事务 */
    abstract public function rollback();
    
    /** 根据不同数据库语法生成分页语句 */
    abstract public function limit($limit, $offset = null);
    
    /** 字符串转义安全处理 */
    abstract public function escape($value);
    
    /** 获取最后发生的错误信息 */
    abstract public function getError();

    /**
     * 获取针对该库的查询构造器
     * @param string $table 表名
     * @return QueryBuilder
     */
    public function table($table) {
        return new QueryBuilder($this, $table);
    }

    /**
     * 检查当前是否已建立连接
     */
    public function isConnected() {
        return $this->connection !== null;
    }
}
