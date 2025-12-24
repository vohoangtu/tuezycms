<?php

declare(strict_types=1);

namespace Modules\Promotion\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Promotion\Application\Service\PromotionService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class PromotionPageController extends BaseController
{
    private PromotionService $promotionService;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        PromotionService $promotionService
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->promotionService = $promotionService;
    }

    /**
     * Show promotions page
     */
    public function index(): void
    {
        $promotions = $this->promotionService->listPromotions();

        $this->render('admin/promotions', [
            'promotions' => $promotions
        ]);
    }
}
