<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Database\DB;

/**
 * Session Timeout Middleware
 * Automatically logs out users after inactivity period
 */
class SessionTimeoutMiddleware
{
    private AuthService $authService;
    private Response $response;

    public function __construct(AuthService $authService, Response $response)
    {
        $this->authService = $authService;
        $this->response = $response;
    }

    /**
     * Check session timeout
     */
    public function handle(Request $request, callable $next): mixed
    {
        // Only check for authenticated users
        if (!$this->authService->isAuthenticated()) {
            return $next($request);
        }

        // Get session timeout configuration
        $config = DB::table('configurations')
            ->where('name', '=', 'session_timeout')
            ->first();

        if (!$config || !$config['is_enabled']) {
            // Session timeout is disabled
            return $next($request);
        }

        $configData = json_decode($config['config'] ?? '{}', true);
        $timeout = (int)($configData['timeout'] ?? 3600); // Default 1 hour

        // Check last activity
        $lastActivity = $_SESSION['last_activity'] ?? time();
        $currentTime = time();

        if (($currentTime - $lastActivity) > $timeout) {
            // Session expired, logout user
            $this->authService->logout();
            
            // Redirect to login with message
            $this->response->redirect('/admin/login?timeout=1');
            return null;
        }

        // Update last activity
        $_SESSION['last_activity'] = $currentTime;

        return $next($request);
    }
}
