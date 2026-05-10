<?php

namespace JThink\Core;

use JThink\Facade\DB;

/**
 * 基础模型类 (ORM)
 * 
 * 职责：提供数据表的抽象表示，封装常用的 CRUD 操作。
 * 通过继承此类，应用模型可以实现面向对象的数据库交互。
 */
abstract class Model {
    /** @var string 对应的数据表名 */
    protected $table;
    
    /** @var string 主键字段名 */
    protected $primaryKey = 'id';
    
    /** @var array 允许批量赋值的字段 */
    protected $fillable = [];
    
    /** @var array 模型的属性数据（对应表字段值） */
    protected $attributes = [];
    
    /** @var bool 标记该记录在数据库中是否已存在 */
    protected $exists = false;

    /**
     * 构造函数
     * @param array $attributes 初始属性数据
     */
    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    /**
     * 批量填充模型属性
     */
    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * 开启一个针对该模型的查询构造器
     * @return QueryBuilder
     */
    public static function query() {
        $instance = new static();
        return DB::table($instance->table);
    }

    /**
     * 根据主键查找记录
     * @param mixed $id
     * @return static|null
     */
    public static function find($id) {
        $instance = new static();
        $data = DB::table($instance->table)->where($instance->primaryKey, $id)->first();
        if ($data) {
            return $instance->newInstance($data, true);
        }
        return null;
    }

    /**
     * 获取表中的所有记录并转换为模型实例列表
     * @return static[]
     */
    public static function all() {
        $instance = new static();
        $results = DB::table($instance->table)->get();
        $models = [];
        foreach ($results as $result) {
            $models[] = $instance->newInstance($result, true);
        }
        return $models;
    }

    /**
     * 保存模型到数据库（新增或更新）
     */
    public function save() {
        if ($this->exists) {
            // 更新已存在的记录
            return DB::table($this->table)
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->update($this->attributes);
        } else {
            // 插入新记录
            $id = DB::table($this->table)->insert($this->attributes);
            if ($id) {
                $this->attributes[$this->primaryKey] = $id;
                $this->exists = true;
                return true;
            }
        }
        return false;
    }

    /**
     * 从数据库中删除当前模型记录
     */
    public function delete() {
        if ($this->exists) {
            return DB::table($this->table)
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->delete();
        }
        return false;
    }

    /**
     * 创建一个新的模型实例，并设置其存在状态
     */
    public function newInstance($attributes = [], $exists = false) {
        $model = new static((array)$attributes);
        $model->exists = $exists;
        return $model;
    }

    /**
     * 魔术方法：读取属性
     */
    public function __get($key) {
        return $this->attributes[$key] ?? null;
    }

    /**
     * 魔术方法：设置属性
     */
    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    /**
     * 将模型属性转换为数组
     */
    public function toArray() {
        return $this->attributes;
    }
}
