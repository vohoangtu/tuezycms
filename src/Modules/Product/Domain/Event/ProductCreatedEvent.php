<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Event;

use Shared\Domain\Event\Event;

/**
 * Product Created Event
 * Fired when a new product is created
 */
class ProductCreatedEvent extends Event
{
    public function __construct(
        private int $productId,
        private string $productName,
        private string $sku
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'product.created';
    }

    public function getPayload(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'sku' => $this->sku,
        ];
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getSku(): string
    {
        return $this->sku;
    }
}
