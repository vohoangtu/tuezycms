<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Container\ServiceContainer;
use Shared\Infrastructure\Middleware\AuthMiddleware;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Exception\ExceptionHandler;
use Shared\Infrastructure\Exception\NotFoundException;

/**
 * Admin Router using RouteRegistry
 */
class AdminRouter
{
    private RouteRegistry $registry;
    private Request $request;
    private Response $response;
    private ServiceContainer $container;
    private ExceptionHandler $exceptionHandler;

    public function __construct(
        Request $request,
        Response $response,
        ServiceContainer $container,
        ExceptionHandler $exceptionHandler
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
        $this->exceptionHandler = $exceptionHandler;
        $this->registry = new RouteRegistry();

        // Register all admin routes
        AdminRoutes::register($this->registry);
    }

    /**
     * Route the request
     *
     * @return void
     */
    public function route(): void
    {
        // Start session using centralized manager
        \Shared\Infrastructure\Session\SessionManager::start();

        // Apply global middlewares
        $this->applyGlobalMiddlewares();

        try {
            // Get path and method
            $path = $this->getPath();
            $method = $this->request->method();

            // Match route
            $route = $this->registry->match($method, $path);

            if ($route === null) {
                throw new NotFoundException("No route found for {$method} {$path}");
            }

            // Apply middleware
            $this->applyMiddleware($route);

            // Execute handler
            $this->executeHandler($route);
        } catch (\Throwable $e) {
            $this->exceptionHandler->handle($e, $this->response);
        }
    }

    /**
     * Get request path
     *
     * @return string
     */
    private function getPath(): string
    {
        $path = $this->request->path();
        $path = str_replace('/admin', '', $path);
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : $path;
    }

    /**
     * Apply middleware
     *
     * @param Route $route
     * @return void
     */
    private function applyMiddleware(Route $route): void
    {
        $middleware = $route->getMiddleware();

        foreach ($middleware as $middlewareClass) {
            if ($middlewareClass === 'auth') {
                $authMiddleware = $this->container->make(AuthMiddleware::class);
                $authMiddleware->setRequestResponse($this->request, $this->response);
                $authMiddleware->requireAuth();
            } elseif ($middlewareClass === 'super_admin') {
                $superAdminMiddleware = $this->container->make(\Shared\Infrastructure\Middleware\SuperAdminMiddleware::class);
                $superAdminMiddleware->setRequestResponse($this->request, $this->response);
                $superAdminMiddleware->requireSuperAdmin();
            } elseif (class_exists($middlewareClass)) {
                $middlewareInstance = $this->container->make($middlewareClass);
                if (method_exists($middlewareInstance, 'handle')) {
                    $middlewareInstance->handle($this->request, function () {
                        // Continue to next middleware
                    });
                }
            }
        }
    }

    /**
     * Execute route handler
     *
     * @param Route $route
     * @return void
     */
    private function executeHandler(Route $route): void
    {
        $handler = $route->getHandler();

        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            
            // Bind Request and Response to container for this request
            $this->container->bind(\Shared\Infrastructure\Http\Request::class, fn() => $this->request);
            $this->container->bind(\Shared\Infrastructure\Http\Response::class, fn() => $this->response);
            
            // Make controller with all dependencies
            $controller = $this->container->make($controllerClass);

            // Call method with route parameters
            $parameters = $route->getParameters();
            if (!empty($parameters)) {
                call_user_func_array([$controller, $method], $parameters);
            } else {
                $controller->$method();
            }
        } elseif (is_callable($handler)) {
            $handler($this->request, $this->response);
        }
    }

    /**
     * Apply global middlewares that run on every request
     */
    private function applyGlobalMiddlewares(): void
    {
        // Maintenance Mode - check first (blocks all non-super-admin users)
        try {
            $maintenanceMiddleware = $this->container->make(\Shared\Infrastructure\Middleware\MaintenanceMiddleware::class);
            $result = $maintenanceMiddleware->handle($this->request, function ($request) {
                return true; // Continue
            });
            
            // If middleware returns null, request was blocked (maintenance page shown)
            if ($result === null) {
                exit;
            }
        } catch (\Exception $e) {
            // If maintenance check fails, continue (fail-open)
            error_log('Maintenance middleware error: ' . $e->getMessage());
        }

        // Session Timeout - check for authenticated users
        try {
            $sessionMiddleware = $this->container->make(\Shared\Infrastructure\Middleware\SessionTimeoutMiddleware::class);
            $sessionMiddleware->handle($this->request, function ($request) {
                return true; // Continue
            });
        } catch (\Exception $e) {
            // If session check fails, continue (fail-open)
            error_log('Session timeout middleware error: ' . $e->getMessage());
        }
    }
}
