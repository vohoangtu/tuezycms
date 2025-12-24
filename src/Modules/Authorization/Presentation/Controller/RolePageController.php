<?php

declare(strict_types=1);

namespace Modules\Authorization\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

/**
 * Role Page Controller
 * 
 * Handles role page rendering
 */
class RolePageController extends BaseController
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
     * Show roles page
     */
    public function index(): void
    {
        $this->render('admin/roles');
    }
}
