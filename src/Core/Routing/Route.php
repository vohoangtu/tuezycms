<?php

declare(strict_types=1);

namespace Core\Routing;

/**
 * Route definition
 */
class Route
{
    private string $method;
    private string $path;
    /**
     * @var callable|array
     */
    private $handler;
    private array $middleware = [];
    private ?string $name = null;
    private array $parameters = [];

    public function __construct(string $method, string $path, callable|array $handler)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get path pattern
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get handler
     *
     * @return callable|array
     */
    public function getHandler(): callable|array
    {
        return $this->handler;
    }

    /**
     * Add middleware
     *
     * @param string|array $middleware
     * @return self
     */
    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array)$middleware);
        return $this;
    }

    /**
     * Get middleware
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Set route name
     *
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get route name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set route parameters
     *
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Get route parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Match route against path
     *
     * @param string $path
     * @return bool
     */
    public function matches(string $path): bool
    {
        $pattern = $this->convertPathToRegex($this->path);
        return preg_match($pattern, $path) === 1;
    }

    /**
     * Extract parameters from path
     *
     * @param string $path
     * @return array
     */
    public function extractParameters(string $path): array
    {
        $pattern = $this->convertPathToRegex($this->path);
        if (preg_match($pattern, $path, $matches)) {
            // Remove full match
            array_shift($matches);
            
            // Filter to only numeric keys (preg_match with named groups returns duplicates)
            // We only want the numeric-indexed values for call_user_func_array
            $numericMatches = [];
            foreach ($matches as $key => $value) {
                if (is_int($key)) {
                    $numericMatches[] = $value;
                }
            }
            
            return $numericMatches;
        }
        return [];
    }

    /**
     * Convert path pattern to regex
     *
     * @param string $path
     * @return string
     */
    private function convertPathToRegex(string $path): string
    {
        // Escape special regex characters except {}
        $pattern = preg_quote($path, '/');
        
        // CRITICAL FIX: Replace {param:\\d+} BEFORE generic {param} pattern
        // After preg_quote, "/roles/{id:\\d+}" becomes "/roles/\{id\:\\d\+\}"
        // In the replacement regex, we need to match these escaped characters
        $pattern = preg_replace('/\\\\{(\w+)\\\\:\\\\\\\\d\\\\\\+\\\\}/', '(?P<$1>\\d+)', $pattern);
        
        // Then replace generic {param} patterns  
        $pattern = preg_replace('/\\\\{(\w+)\\\\}/', '(?P<$1>[^/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
}
