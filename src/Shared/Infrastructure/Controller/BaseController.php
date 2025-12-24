<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;

/**
 * Base Controller
 * 
 * Provides common functionality for all controllers
 */
abstract class BaseController
{
    protected AuthService $authService;
    protected KeyValidator $keyValidator;
    protected Request $request;
    protected Response $response;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response
    ) {
        $this->authService = $authService;
        $this->keyValidator = $keyValidator;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Render a view
     *
     * @param string $view View name
     * @param array $data Data to pass to view
     * @return void
     */
    protected function render(string $view, array $data = []): void
    {
        $this->response->view($view, $data);
    }

    /**
     * Return JSON response
     *
     * @param mixed $data Data to return
     * @param int $status HTTP status code
     * @return void
     */
    protected function json($data, int $status = 200): void
    {
        $this->response->json($data, $status);
    }

    /**
     * Redirect to a URL
     *
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect(string $url): void
    {
        $this->response->redirect($url);
    }

    /**
     * Get current user
     *
     * @return \Modules\User\Domain\Model\User|null
     */
    protected function getCurrentUser(): ?\Modules\User\Domain\Model\User
    {
        return $this->authService->getCurrentUser();
    }
    
    /**
     * Get current user ID
     *
     * @return int|null
     */
    protected function getCurrentUserId(): ?int
    {
        $user = $this->getCurrentUser();
        return $user ? $user->getId() : null;
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return $this->authService->isAuthenticated();
    }

    /**
     * Validate CSRF token
     *
     * @return bool
     */
    protected function validateCsrfToken(): bool
    {
        $token = $this->request->input('csrf_token') ?? $this->request->header('X-CSRF-Token');
        return $this->keyValidator->validateKey($token ?? '');
    }
}
