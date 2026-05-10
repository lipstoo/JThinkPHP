<?php

namespace JThink\Core;

class Container {
    protected $bindings = [];
    protected $instances = [];
    protected $aliases = [];

    public function bind($abstract, $concrete = null, $shared = false) {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        if (!is_callable($concrete)) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    public function singleton($abstract, $concrete = null) {
        $this->bind($abstract, $concrete, true);
    }

    public function instance($abstract, $instance) {
        $this->instances[$abstract] = $instance;
    }

    public function alias($abstract, $alias) {
        $this->aliases[$alias] = $abstract;
    }

    public function make($abstract, $parameters = []) {
        if (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!isset($this->bindings[$abstract])) {
            if (class_exists($abstract)) {
                return $this->build($abstract, $parameters);
            }
            throw new \Exception("Target [$abstract] is not instantiable.");
        }

        $concrete = $this->bindings[$abstract]['concrete'];
        $shared = $this->bindings[$abstract]['shared'];

        $object = $this->build($concrete, $parameters);

        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    protected function build($concrete, $parameters = []) {
        if (is_callable($concrete)) {
            return $concrete($this, $parameters);
        }

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instances = $this->resolveDependencies($dependencies, $parameters);

        return $reflector->newInstanceArgs($instances);
    }

    protected function resolveDependencies($dependencies, $parameters) {
        $results = [];

        foreach ($dependencies as $dependency) {
            $name = $dependency->getName();
            
            if (array_key_exists($name, $parameters)) {
                $results[] = $parameters[$name];
                continue;
            }

            $type = $dependency->getType();

            if ($type && !$type->isBuiltin()) {
                $results[] = $this->make($type->getName());
                continue;
            }

            if ($dependency->isDefaultValueAvailable()) {
                $results[] = $dependency->getDefaultValue();
                continue;
            }

            throw new \Exception("Unable to resolve dependency [$name]");
        }

        return $results;
    }

    protected function getClosure($abstract, $concrete) {
        return function ($container, $parameters) use ($concrete) {
            return $container->build($concrete, $parameters);
        };
    }

    public function has($abstract) {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || isset($this->aliases[$abstract]);
    }

    public function flush() {
        $this->bindings = [];
        $this->instances = [];
        $this->aliases = [];
    }
}
