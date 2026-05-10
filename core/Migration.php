<?php

namespace JThink\Core;

abstract class Migration {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    abstract public function up();
    abstract public function down();
}
