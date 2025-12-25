<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Event;

use Shared\Domain\Event\Event;

/**
 * Product Deleted Event
 * Fired when a product is deleted
 */
class ProductDeletedEvent extends Event
{
    public function __construct(
        private int $productId,
        private string $productName
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'product.deleted';
    }

    public function getPayload(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
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
}
