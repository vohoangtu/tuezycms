<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Database\DB;

/**
 * Maintenance Mode Middleware
 * Blocks access when maintenance mode is enabled (except for Super Admins)
 */
class MaintenanceMiddleware
{
    private AuthService $authService;
    private Response $response;

    public function __construct(AuthService $authService, Response $response)
    {
        $this->authService = $authService;
        $this->response = $response;
    }

    /**
     * Check if maintenance mode is enabled
     */
    public function handle(Request $request, callable $next): mixed
    {
        // Check maintenance_mode configuration
        $config = DB::table('configurations')
            ->where('name', '=', 'maintenance_mode')
            ->first();

        if (!$config || !$config['is_enabled']) {
            // Maintenance mode is off, continue normally
            return $next($request);
        }

        // Check if user is Super Admin
        if ($this->authService->isAuthenticated() && $this->authService->isSuperAdmin()) {
            // Super Admin can access during maintenance
            return $next($request);
        }

        // Show maintenance page
        $this->showMaintenancePage($config);
        return null;
    }

    /**
     * Display maintenance page
     */
    private function showMaintenancePage(array $config): void
    {
        $configData = json_decode($config['config'] ?? '{}', true);
        $message = $configData['message'] ?? 'Website đang trong chế độ bảo trì. Vui lòng quay lại sau.';

        http_response_code(503);
        header('Retry-After: 3600'); // Suggest retry after 1 hour

        echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảo trì hệ thống</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        .icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        p {
            font-size: 1.2rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        .footer {
            margin-top: 2rem;
            font-size: 0.9rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔧</div>
        <h1>Đang bảo trì</h1>
        <p>{$message}</p>
        <div class="footer">
            <p>Cảm ơn sự kiên nhẫn của bạn!</p>
        </div>
    </div>
</body>
</html>
HTML;
        exit;
    }
}
