<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;

/**
 * Super Admin Middleware
 * Ensures only users with super_admin role can access protected routes
 */
class SuperAdminMiddleware
{
    private AuthService $authService;
    private ?Request $request = null;
    private ?Response $response = null;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Set Request and Response (called by router)
     */
    public function setRequestResponse(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Require super admin role
     */
    public function requireSuperAdmin(): void
    {
        // First ensure user is authenticated
        if (!$this->authService->isAuthenticated()) {
            \Shared\Infrastructure\Session\SessionManager::start();
            
            $request = $this->request ?? new Request();
            $_SESSION['redirect_after_login'] = $request->url();
            
            $response = $this->response ?? new Response();
            $response->redirect('/admin/login');
        }

        // Then check if user is super admin
        if (!$this->authService->isSuperAdmin()) {
            throw new \Shared\Infrastructure\Exception\ForbiddenException(
                'Access denied. Super Admin role required.'
            );
        }
    }

    /**
     * Handle middleware (for compatibility with middleware pattern)
     */
    public function handle(Request $request, callable $next): void
    {
        $this->setRequestResponse($request, new Response());
        $this->requireSuperAdmin();
        $next();
    }
}
