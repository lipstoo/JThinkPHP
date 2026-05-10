<?php

namespace JThink\Core;

use JThink\Facade\DB;

class Schema {
    public static function create($table, \Closure $callback) {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->toSql();
        return DB::execute($sql);
    }

    public static function dropIfExists($table) {
        $sql = "DROP TABLE IF EXISTS {$table}";
        return DB::execute($sql);
    }
}

class Blueprint {
    protected $table;
    protected $columns = [];

    public function __construct($table) {
        $this->table = $table;
    }

    public function id() {
        $this->columns[] = "id INT AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    public function string($name, $length = 255) {
        $this->columns[] = "{$name} VARCHAR({$length})";
        return $this;
    }

    public function text($name) {
        $this->columns[] = "{$name} TEXT";
        return $this;
    }

    public function timestamp($name) {
        $this->columns[] = "{$name} TIMESTAMP";
        return $this;
    }

    public function unique() {
        $last = array_pop($this->columns);
        $this->columns[] = $last . " UNIQUE";
        return $this;
    }

    public function timestamps() {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function toSql() {
        $cols = implode(", ", $this->columns);
        return "CREATE TABLE {$this->table} ({$cols})";
    }
}
