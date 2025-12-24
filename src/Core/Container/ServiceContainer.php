<?php

declare(strict_types=1);

namespace Core\Container;

use ReflectionClass;
use ReflectionParameter;
use WeakMap;

/**
 * Service Container with constructor injection and auto-wiring
 * Uses WeakMap for performance optimization
 */
class ServiceContainer
{
    private static ?self $instance = null;
    private WeakMap $services;
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];
    private array $resolving = []; // Track classes being resolved to detect circular dependencies

    private function __construct()
    {
        $this->services = new WeakMap();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Bind an abstract to a concrete implementation
     *
     * @param string $abstract
     * @param string|callable $concrete
     * @return void
     */
    public function bind(string $abstract, string|callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Register a singleton
     *
     * @param string $abstract
     * @param string|callable $concrete
     * @return void
     */
    public function singleton(string $abstract, string|callable $concrete): void
    {
        $this->singletons[$abstract] = $concrete;
    }

    /**
     * Resolve a class with auto-wiring
     *
     * @param string $className
     * @return object
     */
    public function make(string $className): object
    {
        // Check for circular dependency
        if (isset($this->resolving[$className])) {
            throw new \RuntimeException("Circular dependency detected: {$className}. Resolution stack: " . implode(' -> ', array_keys($this->resolving)) . " -> {$className}");
        }

        // Check if it's a singleton
        if (isset($this->instances[$className])) {
            return $this->instances[$className];
        }

        // Mark as being resolved
        $this->resolving[$className] = true;

        try {
            // Check if there's a binding
            if (isset($this->bindings[$className])) {
                $concrete = $this->bindings[$className];
                if (is_callable($concrete)) {
                    $instance = $concrete($this);
                } else {
                    $instance = $this->make($concrete);
                }
            } elseif (isset($this->singletons[$className])) {
                $concrete = $this->singletons[$className];
                if (is_callable($concrete)) {
                    $instance = $concrete($this);
                } else {
                    // If it's a class name string, use resolve() to avoid circular dependency
                    // resolve() uses auto-wiring without checking singletons again
                    $instance = $this->resolve($concrete);
                }
                $this->instances[$className] = $instance;
            } else {
                // Auto-wire
                $instance = $this->resolve($className);
            }

            // Check if it should be a singleton
            if (isset($this->singletons[$className]) && !isset($this->instances[$className])) {
                $this->instances[$className] = $instance;
            }

            return $instance;
        } finally {
            // Remove from resolving stack
            unset($this->resolving[$className]);
        }
    }

    /**
     * Resolve a class using reflection
     *
     * @param string $className
     * @return object
     */
    private function resolve(string $className): object
    {
        if (!class_exists($className)) {
            throw new \RuntimeException("Class {$className} does not exist");
        }

        $reflection = new ReflectionClass($className);

        if (!$reflection->isInstantiable()) {
            throw new \RuntimeException("Class {$className} is not instantiable");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $className();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor dependencies
     *
     * @param array<ReflectionParameter> $parameters
     * @return array
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                // No type hint, try default value
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \RuntimeException("Cannot resolve parameter {$parameter->getName()}");
                }
            } elseif ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                // Class type hint
                $dependencyClass = $type->getName();
                $dependencies[] = $this->make($dependencyClass);
            } else {
                // Built-in type or union/intersection type
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \RuntimeException("Cannot resolve built-in type parameter {$parameter->getName()}");
                }
            }
        }

        return $dependencies;
    }

    /**
     * Get or create a service instance (backward compatibility)
     *
     * @param string $className
     * @return object
     */
    public function get(string $className): object
    {
        return $this->make($className);
    }

    /**
     * Check if service is registered
     *
     * @param string $className
     * @return bool
     */
    public function has(string $className): bool
    {
        return isset($this->bindings[$className]) 
            || isset($this->singletons[$className])
            || isset($this->instances[$className])
            || class_exists($className);
    }
}
