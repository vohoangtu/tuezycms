<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Model;

class OrderItem
{
    private int $productId;
    private string $productName;
    private string $productSku;
    private int $quantity;
    private float $unitPrice;
    private float $totalPrice;

    public function __construct(
        int $productId,
        string $productName,
        string $productSku,
        int $quantity,
        float $unitPrice
    ) {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->productSku = $productSku;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->totalPrice = $quantity * $unitPrice;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getProductSku(): string
    {
        return $this->productSku;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }
}

