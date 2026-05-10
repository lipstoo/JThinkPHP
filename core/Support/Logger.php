<?php

namespace JThink\Core\Support;

class Logger {
    const DEBUG = 100;
    const INFO = 200;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;

    const MAX_FILES = 30;
    const MAX_SIZE = 10485760;

    protected $level;
    protected $path;
    protected $format;
    protected $maxFiles = self::MAX_FILES;
    protected $maxSize = self::MAX_SIZE;

    public function __construct($level = self::DEBUG, $path = null, $format = null) {
        $this->level = $level;
        $this->path = $path ?: (defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : BASE_PATH . '/storage/logs');
        $this->format = $format ?: '[%datetime%] [%level%] %message%';

        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function debug($message, $context = []) {
        $this->log(self::DEBUG, $message, $context);
    }

    public function info($message, $context = []) {
        $this->log(self::INFO, $message, $context);
    }

    public function warning($message, $context = []) {
        $this->log(self::WARNING, $message, $context);
    }

    public function error($message, $context = []) {
        $this->log(self::ERROR, $message, $context);
    }

    public function critical($message, $context = []) {
        $this->log(self::CRITICAL, $message, $context);
    }

    protected function log($level, $message, $context) {
        if ($level < $this->level) {
            return;
        }

        $levelName = $this->getLevelName($level);
        $datetime = date('Y-m-d H:i:s');
        $logMessage = $this->interpolate($message, $context);

        $output = str_replace([
            '%datetime%',
            '%level%',
            '%message%'
        ], [
            $datetime,
            str_pad(strtoupper($levelName), 8),
            $logMessage
        ], $this->format);

        $output .= PHP_EOL;

        $this->write($output);
    }

    protected function write($message) {
        $filename = $this->path . '/' . date('Y-m-d') . '.log';

        $this->rotateIfNeeded($filename);

        file_put_contents($filename, $message, FILE_APPEND | LOCK_EX);
    }

    protected function rotateIfNeeded($filename) {
        if (!file_exists($filename)) {
            return;
        }

        $files = glob($this->path . '/*.log');
        $fileCount = count($files);

        if ($fileCount >= $this->maxFiles) {
            $this->deleteOldLogs($files);
        }

        if (filesize($filename) >= $this->maxSize) {
            $this->rotateFile($filename);
        }
    }

    protected function rotateFile($filename) {
        $datedFilename = $filename;
        $rotatedFilename = $this->path . '/' . date('Y-m-d_H-i-s') . '.log';

        if (file_exists($rotatedFilename)) {
            unlink($rotatedFilename);
        }

        rename($datedFilename, $rotatedFilename);
    }

    protected function deleteOldLogs($files) {
        $fileInfos = [];
        foreach ($files as $file) {
            $fileInfos[] = [
                'path' => $file,
                'mtime' => filemtime($file)
            ];
        }

        usort($fileInfos, function($a, $b) {
            return $a['mtime'] - $b['mtime'];
        });

        $filesToDelete = array_slice($fileInfos, 0, $this->maxFiles / 2);

        foreach ($filesToDelete as $fileInfo) {
            if (file_exists($fileInfo['path'])) {
                unlink($fileInfo['path']);
            }
        }
    }

    protected function interpolate($message, $context) {
        $replace = [];
        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = $this->toString($value);
        }
        return strtr($message, $replace);
    }

    protected function toString($value) {
        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string) $value : get_class($value);
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return (string) $value;
    }

    protected function getLevelName($level) {
        switch ($level) {
            case self::DEBUG:
                return 'debug';
            case self::INFO:
                return 'info';
            case self::WARNING:
                return 'warning';
            case self::ERROR:
                return 'error';
            case self::CRITICAL:
                return 'critical';
            default:
                return 'unknown';
        }
    }

    public function setLevel($level) {
        $this->level = $level;
        return $this;
    }

    public function getLevel() {
        return $this->level;
    }

    public function setMaxFiles($maxFiles) {
        $this->maxFiles = $maxFiles;
        return $this;
    }

    public function setMaxSize($maxSize) {
        $this->maxSize = $maxSize;
        return $this;
    }

    public function clearLogs() {
        foreach (glob($this->path . '/*.log') as $file) {
            if (is_writable($file)) {
                unlink($file);
            }
        }
    }

    public function getLogFiles() {
        $files = glob($this->path . '/*.log');
        $result = [];

        foreach ($files as $file) {
            $result[] = [
                'path' => $file,
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file)
            ];
        }

        usort($result, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $result;
    }

    public function readLog($filename = null, $lines = 100) {
        if ($filename === null) {
            $filename = date('Y-m-d') . '.log';
        }

        $filepath = $this->path . '/' . $filename;

        if (!file_exists($filepath)) {
            return [];
        }

        $file = new \SplFileObject($filepath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key() + 1;

        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);

        $result = [];
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $result[] = $line;
            }
            $file->next();
        }

        return $result;
    }
}
?>