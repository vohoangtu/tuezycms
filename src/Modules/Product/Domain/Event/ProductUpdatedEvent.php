<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Event;

use Shared\Domain\Event\Event;

/**
 * Product Updated Event
 * Fired when a product is updated
 */
class ProductUpdatedEvent extends Event
{
    public function __construct(
        private int $productId,
        private string $productName,
        private array $changes = []
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'product.updated';
    }

    public function getPayload(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'changes' => $this->changes,
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

    public function getChanges(): array
    {
        return $this->changes;
    }
}
