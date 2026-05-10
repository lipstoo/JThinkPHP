<?php

namespace JThink\Core;

use JThink\Facade\DB;

/**
 * 数据库结构管理类 (Schema Builder)
 * 
 * 职责：提供简洁的 API 用于在迁移文件中创建或删除数据库表。
 */
class Schema {
    /**
     * 创建一个新的数据库表
     * 
     * @param string $table 表名
     * @param \Closure $callback 接收 Blueprint 实例的回调函数
     */
    public static function create($table, \Closure $callback) {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->toSql();
        return DB::execute($sql);
    }

    /**
     * 如果表存在则删除
     */
    public static function dropIfExists($table) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return DB::execute($sql);
    }
}

/**
 * 数据表蓝图类
 * 
 * 职责：定义表结构的具体列信息，并将其转化为特定数据库驱动的 SQL。
 */
class Blueprint {
    /** @var string 当前构建的表名 */
    protected $table;
    
    /** @var array 定义的列 SQL 片段集合 */
    protected $columns = [];

    public function __construct($table) {
        $this->table = $table;
    }

    /**
     * 添加自增主键 ID
     */
    public function id() {
        $this->columns[] = "id INT AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    /**
     * 添加字符串字段
     */
    public function string($name, $length = 255) {
        $this->columns[] = "{$name} VARCHAR({$length})";
        return $this;
    }

    /**
     * 添加长文本字段
     */
    public function text($name) {
        $this->columns[] = "{$name} TEXT";
        return $this;
    }

    /**
     * 添加时间戳字段
     */
    public function timestamp($name) {
        $this->columns[] = "{$name} TIMESTAMP";
        return $this;
    }

    /**
     * 设置前一个字段为唯一索引
     */
    public function unique() {
        $last = array_pop($this->columns);
        $this->columns[] = $last . " UNIQUE";
        return $this;
    }

    /**
     * 快速添加 created_at 和 updated_at 字段
     */
    public function timestamps() {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    /**
     * 将定义的蓝图转换为 CREATE TABLE 语句
     */
    public function toSql() {
        $cols = implode(", ", $this->columns);
        return "CREATE TABLE {$this->table} ({$cols})";
    }
}
