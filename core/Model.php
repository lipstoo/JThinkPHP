<?php

namespace JThink\Core;

use JThink\Facade\DB;

abstract class Model {
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $attributes = [];
    protected $exists = false;

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public static function query() {
        $instance = new static();
        return DB::table($instance->table);
    }

    public static function find($id) {
        $instance = new static();
        $data = DB::table($instance->table)->where($instance->primaryKey, $id)->first();
        if ($data) {
            return $instance->newInstance($data, true);
        }
        return null;
    }

    public static function all() {
        $instance = new static();
        $results = DB::table($instance->table)->get();
        $models = [];
        foreach ($results as $result) {
            $models[] = $instance->newInstance($result, true);
        }
        return $models;
    }

    public function save() {
        if ($this->exists) {
            return DB::table($this->table)
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->update($this->attributes);
        } else {
            $id = DB::table($this->table)->insert($this->attributes);
            if ($id) {
                $this->attributes[$this->primaryKey] = $id;
                $this->exists = true;
                return true;
            }
        }
        return false;
    }

    public function delete() {
        if ($this->exists) {
            return DB::table($this->table)
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->delete();
        }
        return false;
    }

    public function newInstance($attributes = [], $exists = false) {
        $model = new static((array)$attributes);
        $model->exists = $exists;
        return $model;
    }

    public function __get($key) {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function toArray() {
        return $this->attributes;
    }
}
