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

        $dispatcher->listen('user.deleted', function($event) use ($clearCacheListener) {
            $clearCacheListener->handleUserDeleted($event);
        });

        // Future: Add more event listeners here
        // $dispatcher->listen('article.published', ...);
        // $dispatcher->listen('order.created', ...);
    }
}
