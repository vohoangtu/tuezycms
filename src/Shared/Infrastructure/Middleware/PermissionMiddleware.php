<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Exception\UnauthorizedException;
use Shared\Infrastructure\Exception\ForbiddenException;

/**
 * Permission Middleware
 * Checks if user has required permission before allowing access
 */
class PermissionMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the middleware
     *
     * @param Request $request
     * @param callable $next
     * @param string $permission Permission name (e.g., 'articles.create')
     * @return mixed
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function handle(Request $request, callable $next, string $permission)
    {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            throw new UnauthorizedException('Authentication required');
        }

        // Check if user has the required permission
        if (!$this->authService->can($permission)) {
            throw new ForbiddenException("Permission required: {$permission}");
        }

        // Continue to next middleware or controller
        return $next($request);
    }

    /**
     * Check permission without throwing exception
     *
     * @param string $permission
     * @return bool
     */
    public function check(string $permission): bool
    {
        if (!$this->authService->isAuthenticated()) {
            return false;
        }

        return $this->authService->can($permission);
    }
}
