<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;

/**
 * Module Controller
 * 
 * Handles module management operations
 */
class ModuleController extends BaseController
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
     * Get all modules
     */
    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        // TODO: Implement get modules logic
        $modules = [];

        $this->json($modules);
    }

    /**
     * Store/update module
     */
    public function store(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $data = $this->request->input();

        // TODO: Implement save module logic

        $this->json(['success' => true]);
    }
}
