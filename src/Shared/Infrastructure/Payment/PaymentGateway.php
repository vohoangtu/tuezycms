<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Payment;

/**
 * Payment Gateway Interface
 * Implementations: VNPay, MoMo, PayPal, etc.
 */
interface PaymentGateway
{
    /**
     * Create payment URL
     */
    public function createPaymentUrl(array $orderData): string;

    /**
     * Verify payment callback
     */
    public function verifyCallback(array $callbackData): bool;

    /**
     * Get transaction ID from callback
     */
    public function getTransactionId(array $callbackData): ?string;

    /**
     * Get payment status from callback
     */
    public function getPaymentStatus(array $callbackData): string;
}

