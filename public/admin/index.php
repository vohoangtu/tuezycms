<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Core\Container\ServiceContainer;
use Core\Container\AppServiceProvider;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Exception\ExceptionHandler;
use Core\Routing\AdminRouter;

// Initialize Service Container
$container = ServiceContainer::getInstance();

// Register services via AppServiceProvider
$appServiceProvider = new AppServiceProvider($container);
$appServiceProvider->register();

try {
    // Set up global exception handling
    $request = $container->make(Request::class);
    $response = $container->make(Response::class);
    $exceptionHandler = $container->make(ExceptionHandler::class);

    set_exception_handler(function (\Throwable $e) use ($exceptionHandler, $response) {
        $exceptionHandler->handle($e, $response);
    });

    // Route admin requests
    $router = new AdminRouter(
        $request,
        $response,
        $container,
        $exceptionHandler
    );
    $router->route();
} catch (\Throwable $e) {
    // Fallback error handling if initialization fails
    $response = new Response();
    $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    $message = $debug ? $e->getMessage() : 'Internal Server Error';
    $details = $debug ? [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ] : null;
    
    $response->json([
        'error' => $message,
        'code' => 500,
        'details' => $details
    ], 500);
}
