<?php

declare(strict_types=1);

namespace Modules\User\Application\Listener;

use Modules\User\Domain\Event\UserCreatedEvent;
use Modules\User\Domain\Event\UserUpdatedEvent;
use Modules\User\Domain\Event\UserDeletedEvent;
use Shared\Infrastructure\Cache\Cache;

/**
 * Clear User Cache Listener
 * Clears user cache when user is created/updated/deleted
 */
class ClearUserCacheListener
{
    public function handleUserCreated(UserCreatedEvent $event): void
    {
        $this->clearCache($event->getPayload()['user_id']);
    }

    public function handleUserUpdated(UserUpdatedEvent $event): void
    {
        $this->clearCache($event->getPayload()['user_id']);
    }

    public function handleUserDeleted(UserDeletedEvent $event): void
    {
        $this->clearCache($event->getUserId());
    }

    private function clearCache(int $userId): void
    {
        // Clear specific user cache
        Cache::delete("user:{$userId}");
        
        // Clear users list cache
        Cache::delete('users:all');
        Cache::delete('users:active');
        
        // Log
        error_log("Cache cleared for user ID: {$userId}");
    }
}
