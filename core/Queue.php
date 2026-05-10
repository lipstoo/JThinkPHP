<?php

namespace JThink\Core;

class Queue {
    const DRIVER_SYNC = 'sync';
    const DRIVER_REDIS = 'redis';
    const DRIVER_DATABASE = 'database';

    protected $driver;
    protected $config = [];
    protected $connection;
    protected $table = 'jobs';

    public function __construct($driver = self::DRIVER_SYNC, $config = []) {
        $this->driver = $driver;
        $this->config = $config;
        $this->table = $config['database']['table'] ?? 'jobs';
    }

    public function push($job, $data = [], $queue = 'default') {
        switch ($this->driver) {
            case self::DRIVER_REDIS:
                return $this->pushRedis($job, $data, $queue);
            case self::DRIVER_DATABASE:
                return $this->pushDatabase($job, $data, $queue);
            default:
                return $this->pushSync($job, $data, $queue);
        }
    }

    public function later($delay, $job, $data = [], $queue = 'default') {
        switch ($this->driver) {
            case self::DRIVER_REDIS:
                return $this->pushRedis($job, $data, $queue, $delay);
            case self::DRIVER_DATABASE:
                return $this->pushDatabase($job, $data, $queue, $delay);
            default:
                return $this->pushSync($job, $data, $queue);
        }
    }

    protected function pushSync($job, $data = [], $queue = 'default') {
        if (is_callable($job)) {
            call_user_func($job, $data);
        } elseif (is_string($job) && class_exists($job)) {
            $instance = new $job();
            if (method_exists($instance, 'handle')) {
                $instance->handle($data);
            }
        }
        return true;
    }

    protected function pushRedis($job, $data = [], $queue = 'default', $delay = 0) {
        $redisConfig = $this->config['redis'] ?? [
            'host' => '127.0.0.1',
            'port' => 6379,
        ];

        try {
            $redis = new \Redis();
            $redis->connect($redisConfig['host'], $redisConfig['port']);

            if (isset($redisConfig['password']) && !empty($redisConfig['password'])) {
                $redis->auth($redisConfig['password']);
            }

            if (isset($redisConfig['database'])) {
                $redis->select($redisConfig['database']);
            }

            $payload = json_encode([
                'job' => $job,
                'data' => $data,
                'queue' => $queue,
                'delay' => $delay,
                'created_at' => time(),
            ]);

            if ($delay > 0) {
                $redis->zAdd('queues:' . $queue . ':delayed', time() + $delay, $payload);
            } else {
                $redis->rPush('queues:' . $queue . ':jobs', $payload);
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Redis connection failed: ' . $e->getMessage());
        }
    }

    protected function pushDatabase($job, $data = [], $queue = 'default', $delay = 0) {
        $payload = json_encode([
            'job' => $job,
            'data' => $data,
            'queue' => $queue,
            'delay' => $delay,
            'created_at' => time(),
        ]);

        $availableAt = $delay > 0 ? time() + $delay : time();

        $record = [
            'queue' => $queue,
            'payload' => $payload,
            'available_at' => $availableAt,
            'created_at' => time(),
            'attempts' => 0,
            'reserved_at' => null,
        ];

        return $this->saveToDatabase($record);
    }

    protected function getDatabaseConnection() {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $dbConfig = $this->config['database'] ?? [];
        $connectionName = $dbConfig['connection'] ?? 'default';

        $this->connection = DBFactory::getConnection($connectionName);
        return $this->connection;
    }

    protected function saveToDatabase($data) {
        try {
            $db = $this->getDatabaseConnection();

            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(',:', array_keys($data));
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

            $db->execute($sql, $data);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Database queue failed: ' . $e->getMessage());
        }
    }

    protected function createJobsTable() {
        try {
            $db = $this->getDatabaseConnection();

            $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                queue VARCHAR(255) NOT NULL,
                payload TEXT NOT NULL,
                available_at INT NOT NULL,
                created_at INT NOT NULL,
                attempts INT DEFAULT 0,
                reserved_at INT DEFAULT NULL,
                INDEX idx_queue_available (queue, available_at),
                INDEX idx_available_at (available_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

            $db->execute($sql);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function pop($queue = 'default') {
        switch ($this->driver) {
            case self::DRIVER_REDIS:
                return $this->popRedis($queue);
            case self::DRIVER_DATABASE:
                return $this->popDatabase($queue);
            default:
                return null;
        }
    }

    protected function popRedis($queue = 'default') {
        $redisConfig = $this->config['redis'] ?? [
            'host' => '127.0.0.1',
            'port' => 6379,
        ];

        try {
            $redis = new \Redis();
            $redis->connect($redisConfig['host'], $redisConfig['port']);

            if (isset($redisConfig['password']) && !empty($redisConfig['password'])) {
                $redis->auth($redisConfig['password']);
            }

            if (isset($redisConfig['database'])) {
                $redis->select($redisConfig['database']);
            }

            $payload = $redis->lPop('queues:' . $queue . ':jobs');

            if (!$payload) {
                $jobs = $redis->zRange('queues:' . $queue . ':delayed', 0, 0);
                if (!empty($jobs)) {
                    foreach ($jobs as $job) {
                        $redis->zRem('queues:' . $queue . ':delayed', $job);
                        $redis->rPush('queues:' . $queue . ':jobs', $job);
                    }
                    $payload = $redis->lPop('queues:' . $queue . ':jobs');
                }
            }

            if ($payload) {
                return json_decode($payload, true);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function popDatabase($queue = 'default') {
        try {
            $db = $this->getDatabaseConnection();

            $now = time();
            $sql = "SELECT * FROM {$this->table}
                    WHERE queue = :queue
                    AND available_at <= :now
                    AND (reserved_at IS NULL OR reserved_at < :timeout)
                    ORDER BY available_at ASC
                    LIMIT 1";

            $reservedAt = time() - 60;
            $job = $db->fetch($sql, [
                'queue' => $queue,
                'now' => $now,
                'timeout' => $reservedAt
            ]);

            if (!$job) {
                return null;
            }

            $updateSql = "UPDATE {$this->table}
                          SET reserved_at = :reserved_at, attempts = attempts + 1
                          WHERE id = :id";
            $db->execute($updateSql, [
                'reserved_at' => time(),
                'id' => $job['id']
            ]);

            return [
                'id' => $job['id'],
                'job' => json_decode($job['payload'], true),
                'attempts' => $job['attempts'] + 1
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function delete($job, $queue = 'default') {
        if ($this->driver === self::DRIVER_DATABASE && isset($job['id'])) {
            try {
                $db = $this->getDatabaseConnection();
                $sql = "DELETE FROM {$this->table} WHERE id = :id";
                $db->execute($sql, ['id' => $job['id']]);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    public function release($job, $queue = 'default', $delay = 0) {
        if ($this->driver === self::DRIVER_DATABASE && isset($job['id'])) {
            try {
                $db = $this->getDatabaseConnection();
                $availableAt = time() + $delay;
                $sql = "UPDATE {$this->table}
                        SET reserved_at = NULL, available_at = :available_at
                        WHERE id = :id";
                $db->execute($sql, [
                    'available_at' => $availableAt,
                    'id' => $job['id']
                ]);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    public function getDriver() {
        return $this->driver;
    }

    public function setDriver($driver) {
        $this->driver = $driver;
        return $this;
    }

    public function getFailedJobs($limit = 50) {
        if ($this->driver !== self::DRIVER_DATABASE) {
            return [];
        }

        try {
            $db = $this->getDatabaseConnection();
            $sql = "SELECT * FROM {$this->table}
                    WHERE attempts >= :max_attempts
                    ORDER BY created_at DESC
                    LIMIT :limit";
            return $db->fetchAll($sql, ['max_attempts' => 3, 'limit' => $limit]);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function clearQueue($queue = 'default') {
        if ($this->driver === self::DRIVER_DATABASE) {
            try {
                $db = $this->getDatabaseConnection();
                $sql = "DELETE FROM {$this->table} WHERE queue = :queue";
                $db->execute($sql, ['queue' => $queue]);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }
}
?>