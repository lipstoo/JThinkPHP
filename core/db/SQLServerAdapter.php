<?php

namespace JThink\Core\DB;

use JThink\Core\Database;
use PDO;
use PDOException;

class SQLServerAdapter extends Database {
    public function connect() {
        $dsn = "sqlsrv:Server={$this->config['host']},{$this->config['port']};Database={$this->config['database']}";
        
        try {
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => $this->config['persistent'] ?? false
                ]
            );
            return true;
        } catch (PDOException $e) {
            throw new \Exception("SQL Server连接失败: " . $e->getMessage());
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

    public function lastInsertId() {
        $result = $this->fetch("SELECT @@IDENTITY as id");
        return $result['id'] ?? 0;
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

    public function escape($value) {
        return $this->connection->quote($value);
    }

    public function getError() {
        $error = $this->connection->errorInfo();
        return $error[2] ?? null;
    }

    public function selectTop($table, $columns, $limit, $where = [], $orderBy = []) {
        $columns = $columns === '*' ? '*' : implode(',', $columns);
        $sql = "SELECT TOP {$limit} {$columns} FROM {$table}";
        
        if (!empty($where)) {
            $whereClause = [];
            foreach ($where as $key => $value) {
                $whereClause[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                $orderParts[] = "{$column} {$direction}";
            }
            $sql .= " ORDER BY " . implode(',', $orderParts);
        }
        
        return $this->fetchAll($sql, $where);
    }

    public function getTotalCount($table, $where = []) {
        $sql = "SELECT COUNT(*) as total FROM {$table}";
        
        if (!empty($where)) {
            $whereClause = [];
            foreach ($where as $key => $value) {
                $whereClause[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $result = $this->fetch($sql, $where);
        return $result['total'] ?? 0;
    }
}
