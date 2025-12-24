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

$orderService = new OrderService();
$paymentGateway = new VNPayGateway();

// Get callback data
$callbackData = $_GET;

// Verify callback
if (!$paymentGateway->verifyCallback($callbackData)) {
    http_response_code(400);
    die('Invalid payment callback');
}

// Get order number from callback
$orderNumber = $callbackData['vnp_TxnRef'] ?? null;
if (!$orderNumber) {
    http_response_code(400);
    die('Order number not found');
}

// Get order
$order = $orderService->getOrderByNumber($orderNumber);
if (!$order) {
    http_response_code(404);
    die('Order not found');
}

// Get payment status
$paymentStatus = $paymentGateway->getPaymentStatus($callbackData);
$transactionId = $paymentGateway->getTransactionId($callbackData);

// Update order payment status
$orderService->updatePaymentStatus(
    $order->getId(),
    $paymentStatus,
    $transactionId
);

// Redirect to order confirmation
header('Location: /order/confirm?order_number=' . urlencode($orderNumber) . '&status=' . urlencode($paymentStatus));
exit;

