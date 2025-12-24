<?php

declare(strict_types=1);

namespace Core\Routing;

/**
 * Route registry for managing routes
 */
class RouteRegistry
{
    /**
     * Registered routes
     *
     * @var array<Route>
     */
    private array $routes = [];

    /**
     * Named routes
     *
     * @var array<string, Route>
     */
    private array $namedRoutes = [];

    /**
     * Route groups
     *
     * @var array
     */
    private array $groups = [];

    /**
     * Add a route
     *
     * @param string $method
     * @param string $path
     * @param callable|array $handler
     * @return Route
     */
    public function addRoute(string $method, string $path, callable|array $handler): Route
    {
        // Apply group prefix
        $prefix = '';
        $middleware = [];
        foreach ($this->groups as $group) {
            if (isset($group['prefix'])) {
                $prefix .= $group['prefix'];
            }
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array)$group['middleware']);
            }
        }
        
        $fullPath = $prefix . $path;
        $route = new Route($method, $fullPath, $handler);
        
        // Apply group middleware
        if (!empty($middleware)) {
            $route->middleware($middleware);
        }
        
        $this->routes[] = $route;

        if ($route->getName()) {
            $this->namedRoutes[$route->getName()] = $route;
        }

        return $route;
    }

    /**
     * Add GET route
     *
     * @param string $path
     * @param callable|array $handler
     * @return Route
     */
    public function get(string $path, callable|array $handler): Route
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add POST route
     *
     * @param string $path
     * @param callable|array $handler
     * @return Route
     */
    public function post(string $path, callable|array $handler): Route
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add PUT route
     *
     * @param string $path
     * @param callable|array $handler
     * @return Route
     */
    public function put(string $path, callable|array $handler): Route
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add DELETE route
     *
     * @param string $path
     * @param callable|array $handler
     * @return Route
     */
    public function delete(string $path, callable|array $handler): Route
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add PATCH route
     *
     * @param string $path
     * @param callable|array $handler
     * @return Route
     */
    public function patch(string $path, callable|array $handler): Route
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * Create a route group
     *
     * @param array $attributes
     * @param callable $callback
     * @return void
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groups[] = $attributes;
        $callback($this);
        array_pop($this->groups);
    }

    /**
     * Match a route
     *
     * @param string $method
     * @param string $path
     * @return Route|null
     */
    public function match(string $method, string $path): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->getMethod() === $method && $route->matches($path)) {
                $parameters = $route->extractParameters($path);
                $route->setParameters($parameters);
                return $route;
            }
        }

        return null;
    }

    /**
     * Get route by name
     *
     * @param string $name
     * @return Route|null
     */
    public function getRouteByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }

    /**
     * Find route by name (alias for getRouteByName)
     *
     * @param string $name
     * @return Route|null
     */
    public function findByName(string $name): ?Route
    {
        return $this->getRouteByName($name);
    }

    /**
     * Get all routes
     *
     * @return array<Route>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}

