<?php

declare(strict_types=1);

namespace Modules\Order\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Order\Application\Service\OrderService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class OrderPageController extends BaseController
{
    private OrderService $orderService;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        OrderService $orderService
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->orderService = $orderService;
    }

    /**
     * Show orders page
     */
    public function index(): void
    {
        $status = $this->request->get('status');
        $orders = $this->orderService->listOrders(100, 0, $status);

        $this->render('admin/orders', [
            'orders' => $orders,
            'currentStatus' => $status
        ]);
    }
}
