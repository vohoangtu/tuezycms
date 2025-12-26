<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Container\ServiceContainer;
use Core\Container\AppServiceProvider;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Exception\ExceptionHandler;
use Core\Routing\Router;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Exception\NotFoundException;

// Initialize Service Container
$container = ServiceContainer::getInstance();

// Register services via AppServiceProvider
$appServiceProvider = new AppServiceProvider($container);
$appServiceProvider->register();

// Register event listeners
\Shared\Infrastructure\Event\EventServiceProvider::register();

// Set up global exception handling
$request = $container->make(Request::class);
$response = $container->make(Response::class);
$exceptionHandler = $container->make(ExceptionHandler::class);

set_exception_handler(function (\Throwable $e) use ($exceptionHandler) {
    $exceptionHandler->handle($e);
});

// Validate source code integrity
$keyValidator = $container->make(KeyValidator::class);
if (!$keyValidator->validateSourceIntegrity()) {
    $response->status(403)->send('Source code integrity check failed. Website disabled.');
}

// Initialize router and translator
// Note: Router is deprecated for matching but used for Locale detection in Controllers
$router = new Router($request);
$translator = Translator::getInstance();
$translator->setLocale($router->getLocale());

// Load Web Routes
// This explicitly loads the routes into the RouteRegistry resolved from container
require_once __DIR__ . '/../routes/web.php';

// Dispatch Request
try {
    $dispatcher = $container->make(\Core\Routing\RouteDispatcher::class);
    $dispatcher->dispatch($request, $response);
} catch (\Throwable $e) {
    $exceptionHandler->handle($e);
}
