<?php

namespace JThink\Core;

/**
 * 依赖注入容器类
 * 
 * 职责：管理对象的生命周期，实现依赖自动解析与注入。
 * 支持单例、普通绑定、别名以及自动递归解析构造函数依赖。
 */
class Container {
    /** @var array 服务绑定定义存储 */
    protected $bindings = [];
    
    /** @var array 已初始化的单例实例存储 */
    protected $instances = [];
    
    /** @var array 服务别名映射 */
    protected $aliases = [];

    /**
     * 注册一个服务绑定
     * 
     * @param string $abstract 抽象名（类名或标识符）
     * @param mixed $concrete 具体实现（类名或闭包）
     * @param bool $shared 是否为单例
     */
    public function bind($abstract, $concrete = null, $shared = false) {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        if (!is_callable($concrete)) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * 注册一个单例服务
     */
    public function singleton($abstract, $concrete = null) {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * 将一个已有的对象实例注册到容器中
     */
    public function instance($abstract, $instance) {
        $this->instances[$abstract] = $instance;
    }

    /**
     * 为服务设置别名
     */
    public function alias($abstract, $alias) {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * 解析并获取服务实例
     * 
     * @param string $abstract 服务名
     * @param array $parameters 实例化时需要的额外参数
     * @return mixed
     */
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

    /**
     * 调用一个回调函数/方法，并自动注入其需要的依赖
     * 
     * @param callable|string $callback 支持 'Class@method' 格式
     * @param array $parameters 额外参数
     */
    public function call($callback, array $parameters = []) {
        if (is_string($callback) && strpos($callback, '@') !== false) {
            $callback = explode('@', $callback);
        }

        if (is_array($callback)) {
            $callback[0] = is_string($callback[0]) ? $this->make($callback[0]) : $callback[0];
            $reflector = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflector = new \ReflectionFunction($callback);
        }

        $dependencies = $reflector->getParameters();
        $instances = $this->resolveDependencies($dependencies, $parameters);

        return call_user_func_array($callback, $instances);
    }

    /**
     * 递归构建对象实例
     */
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

    /**
     * 解析依赖项
     */
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

    /**
     * 获取用于实例化的闭包
     */
    protected function getClosure($abstract, $concrete) {
        return function ($container, $parameters) use ($concrete) {
            return $container->build($concrete, $parameters);
        };
    }

    /**
     * 检查容器中是否存在该服务
     */
    public function has($abstract) {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || isset($this->aliases[$abstract]);
    }

    /**
     * 清空容器所有数据
     */
    public function flush() {
        $this->bindings = [];
        $this->instances = [];
        $this->aliases = [];
    }
}
