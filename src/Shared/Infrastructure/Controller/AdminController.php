<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;

/**
 * Admin Controller
 * 
 * Handles admin dashboard and general admin pages
 */
class AdminController extends BaseController
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
     * Show admin dashboard
     */
    public function dashboard(): void
    {
        $user = $this->getCurrentUser();
        
        // Get dashboard statistics
        $stats = [
            'total_articles' => 0,
            'total_products' => 0,
            'total_orders' => 0,
            'total_users' => 0,
        ];
        
        $this->render('admin/dashboard', [
            'user' => $user,
            'stats' => $stats
        ]);
    }
}
