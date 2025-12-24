<?php

declare(strict_types=1);

namespace Modules\Order\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Order\Application\Service\OrderService;
use Shared\Infrastructure\Exception\NotFoundException;
use Shared\Infrastructure\Exception\BadRequestException;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class OrderController extends BaseController
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

    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $status = $this->request->get('status');
        $orders = $this->orderService->listOrders(100, 0, $status);
        $this->json($orders);
    }

    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $order = $this->orderService->getOrder($id);
        
        if ($order === null) {
            throw new NotFoundException('Order not found');
        }

        $this->json([
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'payment_status' => $order->getPaymentStatus(),
            'total' => $order->getTotal(),
        ]);
    }
}
