<?php

namespace JThink\Core\Database;

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

