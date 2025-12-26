<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Container\ServiceContainer;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Exception\NotFoundException;

/**
 * Route Dispatcher
 * Dispatches requests to registered routes
 */
class RouteDispatcher
{
    private RouteRegistry $routeRegistry;
    private ServiceContainer $container;

    public function __construct(RouteRegistry $routeRegistry, ServiceContainer $container)
    {
        $this->routeRegistry = $routeRegistry;
        $this->container = $container;
    }

    /**
     * Dispatch the request to the appropriate handler
     *
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws NotFoundException
     */
    public function dispatch(Request $request, Response $response): mixed
    {
        $method = $request->method();
        // Remove locale from path for matching
        // Note: Router::getPath() handles this logic but Router is deprecated.
        // We need to handle it or reuse Router logic.
        // For now, let's inject Router to get normalized path? 
        // Or better, let's move that logic to a helper or just do it here.
        // Let's rely on Router::getPath logic which is being moved.
        
        // Simulating Router::getPath() logic here for now
        $path = $this->getNormalizedPath($request);

        $route = $this->routeRegistry->match($method, $path);

        if (!$route) {
            throw new NotFoundException("Route not found for path: {$path}");
        }

        // Execute Middleware (Placeholder - could be expanded)
        // $middleware = $route->getMiddleware();
        // $this->executeMiddleware($middleware, $request, $response);

        $handler = $route->getHandler();
        $params = $route->getParameters();

        // 1. If handler is an array [Controller::class, 'method']
        if (is_array($handler) && count($handler) === 2 && is_string($handler[0])) {
            $controllerClass = $handler[0];
            $methodName = $handler[1];

            // Resolve controller from Container
            $controller = $this->container->make($controllerClass);
            
            // Execute method with params
            return call_user_func_array([$controller, $methodName], $params);
        }

        // 2. If handler is a closure
        if (is_callable($handler)) {
            // Bind params if possible? 
            // Closures in standard routing often assume params are passed as args.
            return call_user_func_array($handler, $params);
        }

        throw new \RuntimeException("Invalid route handler configured.");
    }

     /**
     * Get path without locale prefix (Replicating Router logic)
     */
    private function getNormalizedPath(Request $request): string
    {
        $path = $request->path();
        $path = trim($path, '/');
        
        $segments = explode('/', $path);
        $firstSegment = $segments[0] ?? '';
        
        $supportedLocales = ['vi', 'en'];
        if (in_array($firstSegment, $supportedLocales, true)) {
            array_shift($segments);
            // If segments is empty, it means we are at root but with locale (e.g. /vi)
            // So empty string is correct for matching root route.
            return empty($segments) ? '' : implode('/', $segments);
        }
        
        return $path;
    }
}
