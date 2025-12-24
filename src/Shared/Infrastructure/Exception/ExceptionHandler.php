<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Exception;

use Shared\Infrastructure\Http\Response;
use Throwable;

/**
 * Global exception handler
 */
class ExceptionHandler
{
    private bool $debug;
    private Response $response;

    public function __construct(Response $response, bool $debug = false)
    {
        $this->response = $response;
        $this->debug = $debug;
    }

    /**
     * Handle an exception
     *
     * @param Throwable $exception
     * @param Response|null $response Optional response to use (falls back to stored response)
     * @return void
     */
    public function handle(Throwable $exception, ?Response $response = null): void
    {
        // Use provided response or fall back to stored one, or create new one if needed
        if ($response === null) {
            $response = $this->response;
        }
        
        // If response is null or headers already sent, create a new one
        if ($response === null || headers_sent()) {
            $response = new Response();
        }

        // Handle specific exceptions
        if ($exception instanceof NotFoundException) {
            $response->json([
                'error' => $exception->getMessage(),
                'code' => 404
            ], 404);
            return;
        }

        if ($exception instanceof ValidationException) {
            $response->json([
                'error' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
                'code' => 422
            ], 422);
            return;
        }

        if ($exception instanceof UnauthorizedException) {
            $response->json([
                'error' => $exception->getMessage(),
                'code' => 401
            ], 401);
            return;
        }

        if ($exception instanceof ForbiddenException) {
            $response->json([
                'error' => $exception->getMessage(),
                'code' => 403
            ], 403);
            return;
        }

        if ($exception instanceof BadRequestException) {
            $response->json([
                'error' => $exception->getMessage(),
                'code' => 400
            ], 400);
            return;
        }

        // Handle generic exceptions
        $this->handleGenericException($exception, $response);
    }

    /**
     * Handle generic exceptions
     *
     * @param Throwable $exception
     * @param Response $response
     * @return void
     */
    private function handleGenericException(Throwable $exception, Response $response): void
    {
        $message = 'Internal Server Error';
        $code = 500;
        $details = null;

        if ($this->debug) {
            $message = $exception->getMessage();
            $details = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        // Log the exception
        $this->logException($exception);

        $response->json([
            'error' => $message,
            'code' => $code,
            'details' => $details
        ], $code);
    }

    /**
     * Log exception
     *
     * @param Throwable $exception
     * @return void
     */
    private function logException(Throwable $exception): void
    {
        $logDir = __DIR__ . '/../../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
        $message = sprintf(
            "[%s] %s: %s in %s:%d\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Register as global exception handler
     * 
     * @deprecated Use DI container to create ExceptionHandler instead
     * @param Response $response
     * @param bool $debug
     * @return void
     */
    public static function register(Response $response, bool $debug = false): void
    {
        $handler = new self($response, $debug);

        set_exception_handler(function (Throwable $exception) use ($handler) {
            $handler->handle($exception);
        });

        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}

