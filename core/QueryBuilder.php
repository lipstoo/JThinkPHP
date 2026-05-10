<?php

namespace JThink\Core;

class QueryBuilder {
    protected $db;
    protected $table;
    protected $columns = ['*'];
    protected $where = [];
    protected $orderBy = [];
    protected $groupBy = [];
    protected $having = [];
    protected $join = [];
    protected $limit = null;
    protected $offset = null;
    protected $union = [];
    protected $cacheEnabled = false;
    protected $cacheDuration = 3600;
    protected $cacheKey = null;

    public function __construct($db, $table) {
        $this->db = $db;
        $this->table = $table;
        
        $config = JThink::$config['database'] ?? [];
        $this->cacheEnabled = $config['query_cache'] ?? false;
        $this->cacheDuration = $config['cache_duration'] ?? 3600;
    }

    public function select($columns) {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator = '=', $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    public function whereRaw($sql) {
        $this->where[] = ['raw' => $sql];
        return $this;
    }

    public function orWhere($column, $operator = '=', $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'or' => true];
        return $this;
    }

    public function whereIn($column, $values) {
        $this->where[] = ['column' => $column, 'operator' => 'IN', 'value' => $values];
        return $this;
    }

    public function whereNotIn($column, $values) {
        $this->where[] = ['column' => $column, 'operator' => 'NOT IN', 'value' => $values];
        return $this;
    }

    public function whereBetween($column, $min, $max) {
        $this->where[] = ['column' => $column, 'operator' => 'BETWEEN', 'value' => [$min, $max]];
        return $this;
    }

    public function whereNull($column) {
        $this->where[] = ['column' => $column, 'operator' => 'IS NULL'];
        return $this;
    }

    public function whereNotNull($column) {
        $this->where[] = ['column' => $column, 'operator' => 'IS NOT NULL'];
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = ['column' => $column, 'direction' => strtoupper($direction)];
        return $this;
    }

    public function groupBy($column) {
        $this->groupBy[] = $column;
        return $this;
    }

    public function having($column, $operator = '=', $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->having[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    public function join($table, $first, $operator = '=', $second = null) {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }
        $this->join[] = ['type' => 'INNER', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    public function leftJoin($table, $first, $operator = '=', $second = null) {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }
        $this->join[] = ['type' => 'LEFT', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    public function rightJoin($table, $first, $operator = '=', $second = null) {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }
        $this->join[] = ['type' => 'RIGHT', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function union($query) {
        $this->union[] = ['query' => $query, 'all' => false];
        return $this;
    }

    public function unionAll($query) {
        $this->union[] = ['query' => $query, 'all' => true];
        return $this;
    }

    public function cache($duration = null, $key = null) {
        $this->cacheEnabled = true;
        if ($duration) {
            $this->cacheDuration = $duration;
        }
        if ($key) {
            $this->cacheKey = $key;
        }
        return $this;
    }

    public function get() {
        $sql = $this->buildSelect();
        
        if ($this->cacheEnabled) {
            $key = $this->cacheKey ?? md5($sql);
            $cache = $this->getCache($key);
            
            if ($cache !== false) {
                return $cache;
            }
        }

        $result = $this->db->fetchAll($sql);
        
        if ($this->cacheEnabled) {
            $this->setCache($key, $result);
        }

        return $result;
    }

    public function first() {
        $sql = $this->buildSelect();
        
        if ($this->cacheEnabled) {
            $key = md5('first_' . $sql);
            $cache = $this->getCache($key);
            if ($cache !== false) {
                return $cache;
            }
        }

        $result = $this->db->fetch($sql);
        
        if ($this->cacheEnabled) {
            $this->setCache($key, $result);
        }

        return $result;
    }

    public function count($column = '*') {
        $sql = "SELECT COUNT({$column}) as count FROM {$this->table}" . $this->buildWhere();
        
        if ($this->cacheEnabled) {
            $key = md5('count_' . $sql);
            $cache = $this->getCache($key);
            if ($cache !== false) {
                return $cache;
            }
        }

        $result = $this->db->fetch($sql);
        $count = (int)($result['count'] ?? 0);
        
        if ($this->cacheEnabled) {
            $this->setCache($key, $count);
        }

        return $count;
    }

    public function sum($column) {
        $sql = "SELECT SUM({$column}) as sum FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['sum'] ?? 0;
    }

    public function avg($column) {
        $sql = "SELECT AVG({$column}) as avg FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['avg'] ?? 0;
    }

    public function max($column) {
        $sql = "SELECT MAX({$column}) as max FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['max'] ?? null;
    }

    public function min($column) {
        $sql = "SELECT MIN({$column}) as min FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['min'] ?? null;
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($data) {
        $where = $this->extractWhereParams();
        return $this->db->update($this->table, $data, $where);
    }

    public function delete() {
        $where = $this->extractWhereParams();
        return $this->db->delete($this->table, $where);
    }

    public function paginate($page = 1, $perPage = 15) {
        $offset = ($page - 1) * $perPage;
        $this->limit($perPage)->offset($offset);
        
        $total = $this->count();
        $data = $this->get();
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'has_more' => $page < ceil($total / $perPage)
        ];
    }

    protected function getCache($key) {
        $cacheDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(__DIR__) . '/storage/cache';
        
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . '/query_' . $key . '.php';
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $content = file_get_contents($cacheFile);
        $data = unserialize($content);
        
        if ($data['expire'] > 0 && time() > $data['expire']) {
            unlink($cacheFile);
            return false;
        }
        
        return $data['value'];
    }

    protected function setCache($key, $value) {
        $cacheDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(__DIR__) . '/storage/cache';
        
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . '/query_' . $key . '.php';
        $data = [
            'value' => $value,
            'expire' => $this->cacheDuration > 0 ? time() + $this->cacheDuration : 0
        ];
        
        file_put_contents($cacheFile, serialize($data));
    }

    public static function flushQueryCache() {
        $cacheDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(__DIR__) . '/storage/cache';
        
        foreach (glob($cacheDir . '/query_*.php') as $file) {
            unlink($file);
        }
    }

    protected function buildSelect() {
        $columns = implode(',', $this->columns);
        $sql = "SELECT {$columns} FROM {$this->table}";
        
        $sql .= $this->buildJoin();
        $sql .= $this->buildWhere();
        $sql .= $this->buildGroupBy();
        $sql .= $this->buildHaving();
        $sql .= $this->buildOrderBy();
        $sql .= $this->buildLimit();
        
        return $sql;
    }

    protected function buildJoin() {
        if (empty($this->join)) return '';
        
        $sql = '';
        foreach ($this->join as $join) {
            $type = $join['type'];
            $table = $join['table'];
            $first = $join['first'];
            $operator = $join['operator'];
            $second = $join['second'];
            $sql .= " {$type} JOIN {$table} ON {$first} {$operator} {$second}";
        }
        
        return $sql;
    }

    protected function buildWhere() {
        if (empty($this->where)) return '';
        
        $sql = ' WHERE ';
        $first = true;
        
        foreach ($this->where as $condition) {
            if (!$first) {
                $sql .= isset($condition['or']) && $condition['or'] ? ' OR ' : ' AND ';
            }
            $first = false;
            
            if (isset($condition['raw'])) {
                $sql .= $condition['raw'];
            } else {
                $column = $condition['column'];
                $operator = $condition['operator'];
                
                if ($operator === 'IN' || $operator === 'NOT IN') {
                    $values = implode(',', array_map([$this->db, 'escape'], $condition['value']));
                    $sql .= "{$column} {$operator} ({$values})";
                } elseif ($operator === 'BETWEEN') {
                    $min = $this->db->escape($condition['value'][0]);
                    $max = $this->db->escape($condition['value'][1]);
                    $sql .= "{$column} BETWEEN {$min} AND {$max}";
                } elseif ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
                    $sql .= "{$column} {$operator}";
                } else {
                    $value = $this->db->escape($condition['value']);
                    $sql .= "{$column} {$operator} {$value}";
                }
            }
        }
        
        return $sql;
    }

    protected function buildGroupBy() {
        if (empty($this->groupBy)) return '';
        return ' GROUP BY ' . implode(',', $this->groupBy);
    }

    protected function buildHaving() {
        if (empty($this->having)) return '';
        
        $sql = ' HAVING ';
        $first = true;
        
        foreach ($this->having as $condition) {
            if (!$first) $sql .= ' AND ';
            $first = false;
            
            $column = $condition['column'];
            $operator = $condition['operator'];
            $value = $this->db->escape($condition['value']);
            $sql .= "{$column} {$operator} {$value}";
        }
        
        return $sql;
    }

    protected function buildOrderBy() {
        if (empty($this->orderBy)) return '';
        
        $parts = [];
        foreach ($this->orderBy as $order) {
            $parts[] = "{$order['column']} {$order['direction']}";
        }
        
        return ' ORDER BY ' . implode(',', $parts);
    }

    protected function buildLimit() {
        $sql = '';
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        return $sql;
    }

    protected function extractWhereParams() {
        $params = [];
        foreach ($this->where as $condition) {
            if (!isset($condition['raw']) && isset($condition['column']) && isset($condition['value'])) {
                $params[$condition['column']] = $condition['value'];
            }
        }
        return $params;
    }

    public function reset() {
        $this->columns = ['*'];
        $this->where = [];
        $this->orderBy = [];
        $this->groupBy = [];
        $this->having = [];
        $this->join = [];
        $this->limit = null;
        $this->offset = null;
        $this->union = [];
        $this->cacheKey = null;
        return $this;
    }
}