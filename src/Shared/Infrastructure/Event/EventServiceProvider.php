<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Event;

use Shared\Infrastructure\Event\EventDispatcher;
use Modules\User\Application\Listener\ClearUserCacheListener;

/**
 * Event Service Provider
 * Registers all event listeners
 */
class EventServiceProvider
{
    /**
     * Register all event listeners
     */
    public static function register(): void
    {
        $dispatcher = EventDispatcher::getInstance();
        $clearCacheListener = new ClearUserCacheListener();

        // User events
        $dispatcher->listen('user.created', function($event) use ($clearCacheListener) {
            $clearCacheListener->handleUserCreated($event);
        });

        $dispatcher->listen('user.updated', function($event) use ($clearCacheListener) {
            $clearCacheListener->handleUserUpdated($event);
        });

        $dispatcher->listen('user.deleted', function($event) use ($clearCacheListener) {
            $clearCacheListener->handleUserDeleted($event);
        });

        // Module events
        $moduleListener = new \Modules\Module\Application\Listener\ClearModuleCacheListener();
        $dispatcher->listen('module.toggled', function($event) use ($moduleListener) {
            $moduleListener->handleModuleToggled($event);
        });
        $dispatcher->listen('module.configured', function($event) use ($moduleListener) {
            $moduleListener->handleModuleConfigured($event);
        });

        // Configuration events
        $configListener = new \Shared\Application\Listener\ConfigurationCacheListener();
        $dispatcher->listen('configuration.toggled', function($event) use ($configListener) {
            $configListener->handleConfigurationToggled($event);
        });
        $dispatcher->listen('configuration.updated', function($event) use ($configListener) {
            $configListener->handleConfigurationUpdated($event);
        });

        // Product events
        $productListener = new \Modules\Product\Application\Listener\ClearProductCacheListener();
        $dispatcher->listen('product.created', function($event) use ($productListener) {
            $productListener->handleProductCreated($event);
        });
        $dispatcher->listen('product.updated', function($event) use ($productListener) {
            $productListener->handleProductUpdated($event);
        });
        $dispatcher->listen('product.deleted', function($event) use ($productListener) {
            $productListener->handleProductDeleted($event);
        });

        // Future: Add more event listeners here
        // $dispatcher->listen('article.published', ...);
        // $dispatcher->listen('order.created', ...);
    }
}
