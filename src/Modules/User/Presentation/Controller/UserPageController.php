<?php

declare(strict_types=1);

namespace Modules\User\Presentation\Controller;

use Shared\Infrastructure\Controller\BaseController;
use Modules\Authorization\Infrastructure\Repository\RoleRepository;
use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;

class UserPageController extends BaseController
{
    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        private RoleRepository $roleRepository
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
    }

    /**
     * GET /admin/users
     * Render users management page
     */
    public function index(): void
    {
        // Get all roles for filter
        $roles = $this->roleRepository->findAll();

        $this->render('admin/users', [
            'pageTitle' => 'User Management',
            'roles' => $roles
        ]);
    }
}
