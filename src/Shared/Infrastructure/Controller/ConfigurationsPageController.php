<?php
/**
 * Configurations Page Controller
 * Regular module - can be toggled via Modules
 */

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;

class ConfigurationsPageController extends BaseController
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
     * Show configurations page
     */
    public function index(): void
    {
        $user = $this->authService->getCurrentUser();
        
        $this->render('admin/configurations', [
            'user' => $user
        ]);
    }
}
