<?php

namespace JThink\Core\DB;

use JThink\Core\Database;
use PDO;
use PDOException;

class PostgreSQLAdapter extends Database {
    public function connect() {
        $dsn = "pgsql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};user={$this->config['username']};password={$this->config['password']}";
        
        try {
            $this->connection = new PDO(
                $dsn,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => $this->config['persistent'] ?? false
                ]
            );
            return true;
        } catch (PDOException $e) {
            throw new \Exception("PostgreSQL连接失败: " . $e->getMessage());
        }
    }

    public function disconnect() {
        $this->connection = null;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("SQL执行错误: " . $e->getMessage());
        }
    }

    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(',:', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->execute($sql, $data);
        return $this->lastInsertId();
    }

    public function update($table, $data, $where) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "{$key} = :{$key}";
        }
        $setClause = implode(',', $setClause);
        
        $whereClause = [];
        $whereParams = [];
        foreach ($where as $key => $value) {
            $whereKey = 'where_' . $key;
            $whereClause[] = "{$key} = :{$whereKey}";
            $whereParams[$whereKey] = $value;
        }
        $whereClause = implode(' AND ', $whereClause);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";
        return $this->execute($sql, array_merge($data, $whereParams));
    }

    public function delete($table, $where) {
        $whereClause = [];
        $whereParams = [];
        foreach ($where as $key => $value) {
            $whereKey = 'where_' . $key;
            $whereClause[] = "{$key} = :{$whereKey}";
            $whereParams[$whereKey] = $value;
        }
        $whereClause = implode(' AND ', $whereClause);
        
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        return $this->execute($sql, $whereParams);
    }

    public function lastInsertId($sequenceName = null) {
        if ($sequenceName) {
            return $this->connection->lastInsertId($sequenceName);
        }
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        $this->transaction = true;
        return $this->connection->beginTransaction();
    }

    public function commit() {
        $result = $this->connection->commit();
        $this->transaction = false;
        return $result;
    }

    public function rollback() {
        $result = $this->connection->rollback();
        $this->transaction = false;
        return $result;
    }

    public function limit($limit, $offset = null) {
        $sql = " LIMIT {$limit}";
        if ($offset !== null) {
            $sql .= " OFFSET {$offset}";
        }
        return $sql;
    }

    public function escape($value) {
        return $this->connection->quote($value);
    }

    public function getError() {
        $error = $this->connection->errorInfo();
        return $error[2] ?? null;
    }
}
