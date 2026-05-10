<?php

namespace JThink\Core;

abstract class ServiceProvider {
    protected $container;
    protected $deferred = false;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    abstract public function register();

    public function boot() {
        // 启动逻辑，子类可覆盖
    }

    public function isDeferred() {
        return $this->deferred;
    }

    public function provides() {
        return [];
    }
}
?>