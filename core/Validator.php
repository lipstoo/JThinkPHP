<?php

namespace JThink\Core;

class Validator {
    protected $data;
    protected $rules;
    protected $messages;
    protected $errors = [];

    public function __construct($data, $rules, $messages = []) {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    public static function make($data, $rules, $messages = []) {
        $validator = new self($data, $rules, $messages);
        $validator->validate();
        return $validator;
    }

    public function validate() {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            
            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    protected function applyRule($field, $rule) {
        $params = [];
        
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $value = $this->getValue($field);
        $method = 'validate' . ucfirst($rule);

        if (method_exists($this, $method)) {
            if (!$this->$method($field, $value, $params)) {
                $this->addError($field, $rule, $params);
            }
        }
    }

    protected function getValue($field) {
        return $this->data[$field] ?? null;
    }

    protected function validateRequired($field, $value, $params) {
        return !is_null($value) && $value !== '';
    }

    protected function validateEmail($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateUrl($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateNumeric($field, $value, $params) {
        return is_numeric($value);
    }

    protected function validateInteger($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateFloat($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    protected function validateMin($field, $value, $params) {
        $min = $params[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value >= $min;
        }
        
        if (is_string($value)) {
            return strlen($value) >= $min;
        }
        
        return false;
    }

    protected function validateMax($field, $value, $params) {
        $max = $params[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value <= $max;
        }
        
        if (is_string($value)) {
            return strlen($value) <= $max;
        }
        
        return false;
    }

    protected function validateBetween($field, $value, $params) {
        $min = $params[0] ?? 0;
        $max = $params[1] ?? 0;
        
        if (is_numeric($value)) {
            return $value >= $min && $value <= $max;
        }
        
        if (is_string($value)) {
            $len = strlen($value);
            return $len >= $min && $len <= $max;
        }
        
        return false;
    }

    protected function validateSize($field, $value, $params) {
        $size = $params[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value == $size;
        }
        
        if (is_string($value)) {
            return strlen($value) == $size;
        }
        
        return false;
    }

    protected function validateRegex($field, $value, $params) {
        $pattern = $params[0] ?? '';
        return preg_match($pattern, $value) === 1;
    }

    protected function validateIn($field, $value, $params) {
        return in_array($value, $params);
    }

    protected function validateNotIn($field, $value, $params) {
        return !in_array($value, $params);
    }

    protected function validateConfirmed($field, $value, $params) {
        $confirmedField = $field . '_confirmation';
        return isset($this->data[$confirmedField]) && $value === $this->data[$confirmedField];
    }

    protected function validateUnique($field, $value, $params) {
        return true;
    }

    protected function validateDate($field, $value, $params) {
        return strtotime($value) !== false;
    }

    protected function validatePassword($field, $value, $params) {
        $min = $params[0] ?? 8;
        return strlen($value) >= $min && 
               preg_match('/[A-Z]/', $value) && 
               preg_match('/[a-z]/', $value) && 
               preg_match('/[0-9]/', $value);
    }

    protected function validatePhone($field, $value, $params) {
        return preg_match('/^1[3-9]\d{9}$/', $value) === 1;
    }

    protected function validateIdCard($field, $value, $params) {
        return preg_match('/^\d{17}[\dXx]$/', $value) === 1;
    }

    protected function validateIp($field, $value, $params) {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    protected function validateAlpha($field, $value, $params) {
        return preg_match('/^[a-zA-Z]+$/', $value) === 1;
    }

    protected function validateAlphaNum($field, $value, $params) {
        return preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
    }

    protected function validateJson($field, $value, $params) {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function addError($field, $rule, $params) {
        $key = $field . '.' . $rule;
        
        if (isset($this->messages[$key])) {
            $message = $this->messages[$key];
        } else {
            $message = $this->getDefaultMessage($field, $rule, $params);
        }
        
        $this->errors[$field][] = $message;
    }

    protected function getDefaultMessage($field, $rule, $params) {
        $messages = [
            'required' => "{$field} is required",
            'email' => "{$field} must be a valid email",
            'url' => "{$field} must be a valid URL",
            'numeric' => "{$field} must be a number",
            'integer' => "{$field} must be an integer",
            'float' => "{$field} must be a float",
            'min' => "{$field} must be at least {$params[0]}",
            'max' => "{$field} must be at most {$params[0]}",
            'between' => "{$field} must be between {$params[0]} and {$params[1]}",
            'regex' => "{$field} format is invalid",
            'in' => "{$field} must be one of: " . implode(', ', $params),
            'not_in' => "{$field} must not be one of: " . implode(', ', $params),
            'confirmed' => "{$field} confirmation does not match",
            'unique' => "{$field} already exists",
            'date' => "{$field} must be a valid date",
            'password' => "{$field} must be at least {$params[0]} characters with uppercase, lowercase and numbers",
            'phone' => "{$field} must be a valid phone number",
            'id_card' => "{$field} must be a valid ID card number",
            'ip' => "{$field} must be a valid IP address",
            'alpha' => "{$field} must only contain letters",
            'alpha_num' => "{$field} must only contain letters and numbers",
            'json' => "{$field} must be a valid JSON string",
        ];
        
        return $messages[$rule] ?? "{$field} is invalid";
    }

    public function fails() {
        return !empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }

    public function getError($field) {
        return $this->errors[$field] ?? [];
    }

    public function firstError() {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
}
?>