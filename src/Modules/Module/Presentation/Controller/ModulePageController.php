<?php

declare(strict_types=1);

namespace Modules\Module\Presentation\Controller;

use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Security\KeyValidator;

/**
 * Module Page Controller
 * Renders module management page
 */
class ModulePageController extends BaseController
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
     * GET /admin/modules
     * Show module management page
     */
    public function index(): void
    {
        $user = $this->getCurrentUser();
        
        $this->render('admin/modules', [
            'user' => $user
        ]);
    }
}
