<?php

declare(strict_types=1);

namespace Core\Routing;

/**
 * Route Helper
 * Provides helper functions for route generation
 */
class RouteHelper
{
    private static ?RouteHelper $instance = null;
    private ?RouteRegistry $registry = null;
    private ?Route $currentRoute = null;

    private function __construct()
    {
    }

    public static function getInstance(): RouteHelper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set route registry
     */
    public function setRegistry(RouteRegistry $registry): void
    {
        $this->registry = $registry;
    }

    /**
     * Set current route
     */
    public function setCurrentRoute(?Route $route): void
    {
        $this->currentRoute = $route;
    }

    /**
     * Generate URL by route name
     *
     * @param string $name Route name
     * @param array $params Route parameters
     * @param string|null $locale Locale for URL generation
     * @return string Generated URL
     */
    public function route(string $name, array $params = [], ?string $locale = null): string
    {
        if ($this->registry === null) {
            // Fallback: simple URL generation
            return $this->fallbackRoute($name, $params);
        }

        $route = $this->registry->findByName($name);
        
        if ($route === null) {
            // Fallback if route not found
            return $this->fallbackRoute($name, $params);
        }

        $path = $route->getPath();
        
        // Replace route parameters
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', (string)$value, $path);
            // Also handle regex patterns like {id:\d+}
            $path = preg_replace('/\{' . $key . ':[^}]+\}/', (string)$value, $path);
        }

        // Add locale prefix if needed
        if ($locale && $locale !== 'vi') {
            $path = '/' . $locale . $path;
        }

        // Add query parameters if any remain
        $queryParams = array_diff_key($params, array_flip($this->extractParameterNames($route->getPath())));
        if (!empty($queryParams)) {
            $path .= '?' . http_build_query($queryParams);
        }

        return $path;
    }

    /**
     * Get current route
     *
     * @return Route|null Current route or null
     */
    public function currentRoute(): ?Route
    {
        return $this->currentRoute;
    }

    /**
     * Get current route name
     *
     * @return string|null Current route name or null
     */
    public function currentRouteName(): ?string
    {
        return $this->currentRoute?->getName();
    }

    /**
     * Check if current route matches name
     *
     * @param string $name Route name to check
     * @return bool True if matches
     */
    public function isCurrentRoute(string $name): bool
    {
        return $this->currentRouteName() === $name;
    }

    /**
     * Extract parameter names from route path
     *
     * @param string $path Route path
     * @return array Parameter names
     */
    private function extractParameterNames(string $path): array
    {
        preg_match_all('/\{([^:}]+)/', $path, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Fallback route generation
     *
     * @param string $name Route name/path
     * @param array $params Parameters
     * @return string URL
     */
    private function fallbackRoute(string $name, array $params = []): string
    {
        $url = '/' . ltrim($name, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
}

// Global helper function
if (!function_exists('route')) {
    function route(string $name, array $params = [], ?string $locale = null): string {
        return \Core\Routing\RouteHelper::getInstance()->route($name, $params, $locale);
    }
}
