<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;

/**
 * Settings Page Controller
 * 
 * Handles settings page rendering
 */
class SettingsPageController extends BaseController
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
     * Show settings page
     */
    public function index(): void
    {
        $this->render('admin/settings');
    }
}
