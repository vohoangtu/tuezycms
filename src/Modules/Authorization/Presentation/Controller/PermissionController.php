<?php

declare(strict_types=1);

namespace Modules\Authorization\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

/**
 * Permission Controller
 * 
 * Handles permission API operations
 */
class PermissionController extends BaseController
{
    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
    }

    /**
     * Get all permissions
     */
    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // TODO: Implement get permissions logic
        $permissions = [];

        $this->json($permissions);
    }

    /**
     * Get permissions grouped by resource
     */
    public function byResource(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // TODO: Implement get permissions by resource logic
        $permissions = [];

        $this->json($permissions);
    }

    /**
     * Get a single permission
     */
    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // TODO: Implement get permission logic
        $permission = null;

        if (!$permission) {
            $this->json(['error' => 'Permission not found'], 404);
            return;
        }

        $this->json($permission);
    }
}
