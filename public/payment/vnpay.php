<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use TuzyCMS\Application\Service\OrderService;
use Shared\Infrastructure\Payment\VNPayGateway;
use Shared\Infrastructure\Security\KeyValidator;

$keyValidator = new KeyValidator();
if (!$keyValidator->validateSourceIntegrity()) {
    http_response_code(403);
    die('Source code integrity check failed');
}

$orderId = $_GET['order_id'] ?? null;
if (!$orderId) {
    http_response_code(400);
    die('Order ID required');
}

$orderService = new OrderService();
$order = $orderService->getOrder((int)$orderId);

if (!$order) {
    http_response_code(404);
    die('Order not found');
}

$paymentGateway = new VNPayGateway();

$orderData = [
    'order_number' => $order->getOrderNumber(),
    'total' => $order->getTotal(),
    'bank_code' => $_GET['bank_code'] ?? '',
];

$paymentUrl = $paymentGateway->createPaymentUrl($orderData);

// Redirect to payment gateway
header('Location: ' . $paymentUrl);
exit;

