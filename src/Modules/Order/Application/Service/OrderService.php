<?php

declare(strict_types=1);

namespace Modules\Order\Application\Service;

use TuzyCMS\Domain\Order\Order;
use TuzyCMS\Domain\Order\OrderAddress;
use TuzyCMS\Domain\Order\OrderItem;
use Modules\Order\Infrastructure\Repository\OrderRepository;
use Modules\Product\Infrastructure\Repository\ProductRepository;
use TuzyCMS\Infrastructure\Repository\PromotionRepository;

class OrderService
{
    private OrderRepository $orderRepository;
    private ProductRepository $productRepository;
    private PromotionRepository $promotionRepository;
    private CartService $cartService;
    private WarehouseService $warehouseService;

    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        PromotionRepository $promotionRepository,
        CartService $cartService,
        WarehouseService $warehouseService
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->promotionRepository = $promotionRepository;
        $this->cartService = $cartService;
        $this->warehouseService = $warehouseService;
    }

    /**
     * Create order from cart
     */
    public function createOrder(
        int $customerId,
        array $cartItems,
        string $paymentMethod,
        array $shippingAddressData,
        array $billingAddressData,
        ?string $promotionCode = null,
        ?string $notes = null
    ): Order {
        // Validate cart items
        $validation = $this->cartService->validateCartItems($cartItems);
        
        if (!empty($validation['errors'])) {
            throw new \RuntimeException('Cart validation failed: ' . implode(', ', $validation['errors']));
        }

        // Calculate totals
        $totals = $this->cartService->calculateTotals($validation['items'], $promotionCode);

        // Apply promotion if code provided
        $discount = 0.0;
        if ($promotionCode) {
            $promotion = $this->promotionRepository->findByCode($promotionCode);
            if ($promotion && $promotion->isValid(new \DateTimeImmutable())) {
                $discount = $promotion->calculateDiscount($totals['subtotal']);
                $totals['discount'] = $discount;
                $totals['total'] = $totals['subtotal'] + $totals['shippingFee'] - $discount;
            }
        }

        // Create order items
        $orderItems = [];
        foreach ($totals['items'] as $item) {
            $orderItems[] = new OrderItem(
                $item['productId'],
                $item['productName'],
                $item['productSku'],
                $item['quantity'],
                $item['unitPrice']
            );
        }

        // Create addresses
        $shippingAddress = new OrderAddress(
            $shippingAddressData['fullName'],
            $shippingAddressData['phone'],
            $shippingAddressData['email'],
            $shippingAddressData['address'],
            $shippingAddressData['ward'],
            $shippingAddressData['district'],
            $shippingAddressData['province'],
            $shippingAddressData['postalCode'] ?? null
        );

        $billingAddress = new OrderAddress(
            $billingAddressData['fullName'],
            $billingAddressData['phone'],
            $billingAddressData['email'],
            $billingAddressData['address'],
            $billingAddressData['ward'],
            $billingAddressData['district'],
            $billingAddressData['province'],
            $billingAddressData['postalCode'] ?? null
        );

        // Generate order number
        $orderNumber = $this->generateOrderNumber();

        // Create order
        $order = new Order(
            $orderNumber,
            $customerId,
            $orderItems,
            $totals['subtotal'],
            $totals['total'],
            $paymentMethod,
            $shippingAddress,
            $billingAddress
        );

        $order->setShippingFee($totals['shippingFee']);
        $order->setDiscount($discount);
        if ($notes) {
            $order->setNotes($notes);
        }

        // Save order
        $this->orderRepository->save($order);

        // Update stock in warehouse
        foreach ($validation['items'] as $item) {
            /** @var \Modules\Product\Domain\Model\Product $product */
            $product = $item['product'];
            $quantity = $item['quantity'];
            
            // Reduce product stock
            $product->reduceStock($quantity);
            $this->productRepository->save($product);

            // Record warehouse transaction
            $this->warehouseService->recordOutgoing(
                $product->getId(),
                $quantity,
                'order',
                $order->getId(),
                "Order #{$orderNumber}"
            );
        }

        // Increment promotion usage if applied
        if ($promotionCode) {
            $promotion = $this->promotionRepository->findByCode($promotionCode);
            if ($promotion) {
                $promotion->incrementUsedCount();
                $this->promotionRepository->save($promotion);
            }
        }

        return $order;
    }

    /**
     * Get order by ID
     */
    public function getOrder(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    /**
     * Get order by order number
     */
    public function getOrderByNumber(string $orderNumber): ?Order
    {
        return $this->orderRepository->findByOrderNumber($orderNumber);
    }

    /**
     * List orders for customer
     */
    public function listCustomerOrders(int $customerId, int $limit = 50, int $offset = 0): array
    {
        return $this->orderRepository->findByCustomerId($customerId, $limit, $offset);
    }

    /**
     * List all orders (admin)
     */
    public function listOrders(int $limit = 100, int $offset = 0, ?string $status = null): array
    {
        return $this->orderRepository->findAll($limit, $offset, $status);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(int $orderId, string $status): Order
    {
        $order = $this->orderRepository->findById($orderId);
        if ($order === null) {
            throw new \RuntimeException('Order not found.');
        }

        $order->setStatus($status);
        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $orderId, string $paymentStatus, ?string $transactionId = null): Order
    {
        $order = $this->orderRepository->findById($orderId);
        if ($order === null) {
            throw new \RuntimeException('Order not found.');
        }

        $order->setPaymentStatus($paymentStatus);
        if ($transactionId) {
            $order->setPaymentTransactionId($transactionId);
        }
        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        return "{$prefix}-{$date}-{$random}";
    }
}

