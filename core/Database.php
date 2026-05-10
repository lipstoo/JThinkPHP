<?php

namespace JThink\Core;

abstract class Database {
    protected $config = [];
    protected $connection = null;
    protected $transaction = false;

    public function __construct($config) {
        $this->config = $config;
    }

    abstract public function connect();
    abstract public function disconnect();
    abstract public function query($sql, $params = []);
    abstract public function execute($sql, $params = []);
    abstract public function fetch($sql, $params = []);
    abstract public function fetchAll($sql, $params = []);
    abstract public function insert($table, $data);
    abstract public function update($table, $data, $where);
    abstract public function delete($table, $where);
    abstract public function lastInsertId();
    abstract public function beginTransaction();
    abstract public function commit();
    abstract public function rollback();
    abstract public function limit($limit, $offset = null);
    abstract public function escape($value);
    abstract public function getError();

    public function table($table) {
        return new QueryBuilder($this, $table);
    }

    public function isConnected() {
        return $this->connection !== null;
    }
}
