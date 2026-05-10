<?php

namespace JThink\Core\Database;

use JThink\Core\Foundation\JThink;

/**
 * 数据库查询构造器
 * 
 * 职责：提供流式的、类型安全的 SQL 查询构建接口。
 * 支持链式调用、多数据库语法适配、查询缓存以及复杂的连接和聚合操作。
 */
class QueryBuilder {
    /** @var Database 数据库驱动实例 */
    protected $db;
    
    /** @var string 当前操作的表名 */
    protected $table;
    
    /** @var array 查询列 */
    protected $columns = ['*'];
    
    /** @var array 查询条件 */
    protected $where = [];
    
    /** @var array 排序规则 */
    protected $orderBy = [];
    
    /** @var array 分组规则 */
    protected $groupBy = [];
    
    /** @var array 分组过滤条件 */
    protected $having = [];
    
    /** @var array 表连接信息 */
    protected $join = [];
    
    /** @var int|null 限制数量 */
    protected $limit = null;
    
    /** @var int|null 偏移量 */
    protected $offset = null;
    
    /** @var array 联合查询 */
    protected $union = [];
    
    /** @var bool 是否启用查询结果缓存 */
    protected $cacheEnabled = false;
    
    /** @var int 缓存时长（秒） */
    protected $cacheDuration = 3600;
    
    /** @var string|null 自定义缓存键 */
    protected $cacheKey = null;

    /**
     * 初始化查询构造器
     */
    public function __construct($db, $table) {
        $this->db = $db;
        $this->table = $table;
        
        $config = JThink::$config['database'] ?? [];
        $this->cacheEnabled = $config['query_cache'] ?? false;
        $this->cacheDuration = $config['cache_duration'] ?? 3600;
    }

    /**
     * 设置查询的字段
     */
    public function select($columns) {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * 添加 WHERE 条件
     */
    public function where($column, $operator = '=', $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    /**
     * 添加原生 WHERE 字符串
     */
    public function whereRaw($sql) {
        $this->where[] = ['raw' => $sql];
        return $this;
    }

    /**
     * 添加 OR WHERE 条件
     */
    public function orWhere($column, $operator = '=', $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'or' => true];
        return $this;
    }

    /**
     * WHERE IN 条件
     */
    public function whereIn($column, $values) {
        $this->where[] = ['column' => $column, 'operator' => 'IN', 'value' => $values];
        return $this;
    }

    /**
     * WHERE NOT IN 条件
     */
    public function whereNotIn($column, $values) {
        $this->where[] = ['column' => $column, 'operator' => 'NOT IN', 'value' => $values];
        return $this;
    }

    /**
     * 范围查询
     */
    public function whereBetween($column, $min, $max) {
        $this->where[] = ['column' => $column, 'operator' => 'BETWEEN', 'value' => [$min, $max]];
        return $this;
    }

    /**
     * 检查是否为 NULL
     */
    public function whereNull($column) {
        $this->where[] = ['column' => $column, 'operator' => 'IS NULL'];
        return $this;
    }

    /**
     * 检查是否不为 NULL
     */
    public function whereNotNull($column) {
        $this->where[] = ['column' => $column, 'operator' => 'IS NOT NULL'];
        return $this;
    }

    /**
     * 设置排序规则
     */
    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = ['column' => $column, 'direction' => strtoupper($direction)];
        return $this;
    }

    /**
     * 分组
     */
    public function groupBy($column) {
        $this->groupBy[] = $column;
        return $this;
    }

    /**
     * 设置分组过滤
     */
    public function having($column, $operator = '=', $value = null) {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->having[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    /**
     * 内连接
     */
    public function join($table, $first, $operator = '=', $second = null) {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }
        $this->join[] = ['type' => 'INNER', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    /**
     * 左连接
     */
    public function leftJoin($table, $first, $operator = '=', $second = null) {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }
        $this->join[] = ['type' => 'LEFT', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    /**
     * 右连接
     */
    public function rightJoin($table, $first, $operator = '=', $second = null) {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }
        $this->join[] = ['type' => 'RIGHT', 'table' => $table, 'first' => $first, 'operator' => $operator, 'second' => $second];
        return $this;
    }

    /**
     * 限制返回数量
     */
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 设置偏移量
     */
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * 联合查询 (UNION)
     */
    public function union($query) {
        $this->union[] = ['query' => $query, 'all' => false];
        return $this;
    }

    /**
     * 联合查询 (UNION ALL)
     */
    public function unionAll($query) {
        $this->union[] = ['query' => $query, 'all' => true];
        return $this;
    }

    /**
     * 开启查询缓存
     * @param int $duration 缓存秒数
     * @param string $key 强制指定缓存键名
     */
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

    /**
     * 执行查询并获取所有结果
     */
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

    /**
     * 执行查询并获取第一条结果
     */
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

    /**
     * 统计记录数
     */
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

    /**
     * 求和
     */
    public function sum($column) {
        $sql = "SELECT SUM({$column}) as sum FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['sum'] ?? 0;
    }

    /**
     * 平均值
     */
    public function avg($column) {
        $sql = "SELECT AVG({$column}) as avg FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['avg'] ?? 0;
    }

    /**
     * 最大值
     */
    public function max($column) {
        $sql = "SELECT MAX({$column}) as max FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['max'] ?? null;
    }

    /**
     * 最小值
     */
    public function min($column) {
        $sql = "SELECT MIN({$column}) as min FROM {$this->table}" . $this->buildWhere();
        $result = $this->db->fetch($sql);
        return $result['min'] ?? null;
    }

    /**
     * 插入数据
     */
    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    /**
     * 更新数据（根据当前 where 条件）
     */
    public function update($data) {
        $where = $this->extractWhereParams();
        return $this->db->update($this->table, $data, $where);
    }

    /**
     * 删除数据（根据当前 where 条件）
     */
    public function delete() {
        $where = $this->extractWhereParams();
        return $this->db->delete($this->table, $where);
    }

    /**
     * 分页查询
     * @param int $page 当前页码
     * @param int $perPage 每页数量
     * @return array 包含数据及分页元信息的数组
     */
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

    /**
     * 从本地文件获取查询缓存
     */
    protected function getCache($key) {
        $cacheDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(dirname(__DIR__)) . '/storage/cache';
        
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

    /**
     * 写入查询缓存到本地文件
     */
    protected function setCache($key, $value) {
        $cacheDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(dirname(__DIR__)) . '/storage/cache';
        
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

    /**
     * 清空所有查询缓存文件
     */
    public static function flushQueryCache() {
        $cacheDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/cache' : dirname(dirname(__DIR__)) . '/storage/cache';
        
        foreach (glob($cacheDir . '/query_*.php') as $file) {
            unlink($file);
        }
    }

    /**
     * 核心：构建 SELECT 语句
     */
    protected function buildSelect() {
        $columns = implode(',', $this->columns);
        $limitSql = $this->buildLimit();
        
        // 特殊处理 SQL Server 的 TOP 语法（出现在 SELECT 之后）
        $top = '';
        if (strpos($limitSql, ' TOP ') === 0) {
            $top = $limitSql;
            $limitSql = '';
        }
        
        $sql = "SELECT{$top} {$columns} FROM {$this->table}";
        
        $sql .= $this->buildJoin();
        $sql .= $this->buildWhere();
        $sql .= $this->buildGroupBy();
        $sql .= $this->buildHaving();
        $sql .= $this->buildOrderBy();
        $sql .= $limitSql;
        
        return $sql;
    }

    /**
     * 构建 JOIN 子句
     */
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

    /**
     * 构建 WHERE 子句
     */
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

    /**
     * 构建 GROUP BY 子句
     */
    protected function buildGroupBy() {
        if (empty($this->groupBy)) return '';
        return ' GROUP BY ' . implode(',', $this->groupBy);
    }

    /**
     * 构建 HAVING 子句
     */
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

    /**
     * 构建 ORDER BY 子句
     */
    protected function buildOrderBy() {
        if (empty($this->orderBy)) return '';
        
        $parts = [];
        foreach ($this->orderBy as $order) {
            $parts[] = "{$order['column']} {$order['direction']}";
        }
        
        return ' ORDER BY ' . implode(',', $parts);
    }

    /**
     * 构建 LIMIT/OFFSET 子句（委托给数据库驱动处理）
     */
    protected function buildLimit() {
        if ($this->limit === null) return '';
        return $this->db->limit($this->limit, $this->offset);
    }

    /**
     * 提取当前 where 条件中的参数（用于 update/delete）
     */
    protected function extractWhereParams() {
        $params = [];
        foreach ($this->where as $condition) {
            if (!isset($condition['raw']) && isset($condition['column']) && isset($condition['value'])) {
                $params[$condition['column']] = $condition['value'];
            }
        }
        return $params;
    }

    /**
     * 重置查询构造器状态
     */
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