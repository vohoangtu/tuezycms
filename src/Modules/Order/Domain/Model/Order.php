<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Model;

use DateTimeImmutable;

class Order
{
    private ?int $id = null;
    private string $orderNumber;
    private int $customerId;
    private array $items = [];
    private float $subtotal;
    private float $shippingFee = 0.0;
    private float $discount = 0.0;
    private float $total;
    private string $status = 'pending';
    private string $paymentStatus = 'unpaid';
    private string $paymentMethod;
    private ?string $paymentTransactionId = null;
    private OrderAddress $shippingAddress;
    private OrderAddress $billingAddress;
    private ?string $notes = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $orderNumber,
        int $customerId,
        array $items,
        float $subtotal,
        float $total,
        string $paymentMethod,
        OrderAddress $shippingAddress,
        OrderAddress $billingAddress
    ) {
        $this->orderNumber = $orderNumber;
        $this->customerId = $customerId;
        $this->items = $items;
        $this->subtotal = $subtotal;
        $this->total = $total;
        $this->paymentMethod = $paymentMethod;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function getShippingFee(): float
    {
        return $this->shippingFee;
    }

    public function setShippingFee(float $shippingFee): void
    {
        $this->shippingFee = $shippingFee;
        $this->recalculateTotal();
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
        $this->recalculateTotal();
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    private function recalculateTotal(): void
    {
        $this->total = $this->subtotal + $this->shippingFee - $this->discount;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): void
    {
        $this->paymentStatus = $paymentStatus;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getPaymentTransactionId(): ?string
    {
        return $this->paymentTransactionId;
    }

    public function setPaymentTransactionId(?string $paymentTransactionId): void
    {
        $this->paymentTransactionId = $paymentTransactionId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getShippingAddress(): OrderAddress
    {
        return $this->shippingAddress;
    }

    public function getBillingAddress(): OrderAddress
    {
        return $this->billingAddress;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

