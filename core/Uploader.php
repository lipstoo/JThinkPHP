<?php

namespace JThink\Core;

class Uploader {
    protected $config = [];
    protected $errors = [];

    public function __construct($config = []) {
        $this->config = array_merge([
            'max_size' => 2097152,
            'upload_path' => 'storage/uploads',
            'allowed_types' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf',
            ],
            'encrypt_name' => true,
        ], $config);
    }

    public function upload($file, $path = null) {
        $this->errors = [];

        if (!$this->isUploaded($file)) {
            $this->errors[] = 'No file was uploaded';
            return false;
        }

        if (!$this->validateSize($file)) {
            $this->errors[] = 'File size exceeds maximum allowed size';
            return false;
        }

        if (!$this->validateType($file)) {
            $this->errors[] = 'File type is not allowed';
            return false;
        }

        $uploadPath = $path ?? $this->config['upload_path'];
        $uploadDir = defined('BASE_PATH') ? BASE_PATH . '/' . $uploadPath : $uploadPath;

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = $this->getFilename($file);
        $destination = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'path' => $uploadPath . '/' . $filename,
                'filename' => $filename,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type'],
            ];
        }

        $this->errors[] = 'Failed to move uploaded file';
        return false;
    }

    public function uploadMultiple($files, $path = null) {
        $results = [];

        foreach ($files as $file) {
            $result = $this->upload($file, $path);
            $results[] = $result;
        }

        return $results;
    }

    protected function isUploaded($file) {
        return isset($file['error']) && $file['error'] === UPLOAD_ERR_OK;
    }

    protected function validateSize($file) {
        return $file['size'] <= $this->config['max_size'];
    }

    protected function validateType($file) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        return in_array($mimeType, $this->config['allowed_types']);
    }

    protected function getFilename($file) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if ($this->config['encrypt_name']) {
            return md5(uniqid(mt_rand(), true)) . '.' . strtolower($extension);
        }
        
        return $file['name'];
    }

    public function delete($path) {
        $fullPath = defined('BASE_PATH') ? BASE_PATH . '/' . $path : $path;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getLastError() {
        return $this->errors[0] ?? null;
    }

    public function setConfig($config) {
        $this->config = array_merge($this->config, $config);
    }
}
?>