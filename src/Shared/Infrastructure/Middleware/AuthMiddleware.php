<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Middleware;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Exception\UnauthorizedException;

class AuthMiddleware
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
     * Require authentication
     */
    public function requireAuth(): void
    {
        if (!$this->authService->isAuthenticated()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $request = $this->request ?? new Request();
            $_SESSION['redirect_after_login'] = $request->url();
            
            $response = $this->response ?? new Response();
            $response->redirect('/admin/login');
        }
    }

    /**
     * Require admin role
     */
    public function requireAdmin(): void
    {
        $this->requireAuth();
        
        if (!$this->authService->isAdmin()) {
            throw new \Shared\Infrastructure\Exception\ForbiddenException('Access denied. Admin role required.');
        }
    }

    /**
     * Redirect to dashboard if already authenticated
     */
    public function redirectIfAuthenticated(): void
    {
        if ($this->authService->isAuthenticated()) {
            $response = $this->response ?? new Response();
            $response->redirect('/admin');
        }
    }

    /**
     * Require a specific permission
     *
     * @param string $permission Permission name (e.g., 'articles.create')
     * @throws UnauthorizedException
     * @throws \Shared\Infrastructure\Exception\ForbiddenException
     */
    public function requirePermission(string $permission): void
    {
        $this->requireAuth();
        
        if (!$this->authService->can($permission)) {
            throw new \Shared\Infrastructure\Exception\ForbiddenException(
                "Access denied. Permission required: {$permission}"
            );
        }
    }

    /**
     * Require a specific role
     *
     * @param string $roleName Role name (e.g., 'admin')
     * @throws UnauthorizedException
     * @throws \Shared\Infrastructure\Exception\ForbiddenException
     */
    public function requireRole(string $roleName): void
    {
        $this->requireAuth();
        
        if (!$this->authService->hasRole($roleName)) {
            throw new \Shared\Infrastructure\Exception\ForbiddenException(
                "Access denied. Role required: {$roleName}"
            );
        }
    }
}
