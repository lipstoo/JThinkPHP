<?php

namespace JThink\Core\Database;

/**
 * 迁移抽象基类
 * 
 * 职责：定义数据库版本控制的结构规范。
 * 每个迁移文件必须实现 up() 用于升级结构，以及 down() 用于回滚结构。
 */
abstract class Migration {
    /** @var Database 数据库连接实例 */
    protected $db;

    /**
     * 初始化迁移类
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 执行迁移操作（创建表、添加字段等）
     */
    abstract public function up();

    /**
     * 撤销迁移操作（删除表、移除字段等）
     */
    abstract public function down();
}
