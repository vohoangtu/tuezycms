<?php

declare(strict_types=1);

namespace Modules\Product\Application\Listener;

use Modules\Product\Domain\Event\ProductCreatedEvent;
use Modules\Product\Domain\Event\ProductUpdatedEvent;
use Modules\Product\Domain\Event\ProductDeletedEvent;
use Shared\Infrastructure\Cache\Cache;

/**
 * Clear Product Cache Listener
 * Clears product cache when product is created/updated/deleted
 */
class ClearProductCacheListener
{
    public function handleProductCreated(ProductCreatedEvent $event): void
    {
        $this->clearCache($event->getProductId());
        error_log("Product created: {$event->getProductName()} (ID: {$event->getProductId()})");
    }

    public function handleProductUpdated(ProductUpdatedEvent $event): void
    {
        $this->clearCache($event->getProductId());
        error_log("Product updated: {$event->getProductName()} (ID: {$event->getProductId()})");
    }

    public function handleProductDeleted(ProductDeletedEvent $event): void
    {
        $this->clearCache($event->getProductId());
        error_log("Product deleted: {$event->getProductName()} (ID: {$event->getProductId()})");
    }

    private function clearCache(int $productId): void
    {
        // Clear specific product cache
        Cache::delete("product:{$productId}");
        
        // Clear products list cache
        Cache::delete('products:all');
        Cache::delete('products:active');
        Cache::delete('products:featured');
        
        error_log("Cache cleared for product ID: {$productId}");
    }
}
