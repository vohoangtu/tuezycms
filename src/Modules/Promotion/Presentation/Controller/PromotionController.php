<?php

declare(strict_types=1);

namespace Modules\Promotion\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Promotion\Application\Service\PromotionService;
use Shared\Infrastructure\Exception\NotFoundException;
use Shared\Infrastructure\Exception\BadRequestException;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class PromotionController extends BaseController
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

    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $promotions = $this->promotionService->listPromotions();
        $this->json($promotions);
    }

    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $promotion = $this->promotionService->getPromotion($id);
        
        if ($promotion === null) {
            throw new NotFoundException('Promotion not found');
        }

        $this->json([
            'id' => $promotion->getId(),
            'name' => $promotion->getName(),
            'code' => $promotion->getCode(),
            'type' => $promotion->getType()->value,
            'value' => $promotion->getValue(),
        ]);
    }
}
