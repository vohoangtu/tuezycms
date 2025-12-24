<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Event;

use Shared\Domain\Event\EventInterface;

/**
 * Event Dispatcher
 * Manages event listeners and dispatches events
 */
class EventDispatcher
{
    private static ?EventDispatcher $instance = null;
    private array $listeners = [];

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reset instance (for testing)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Listen to an event
     */
    public function listen(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Dispatch an event
     */
    public function dispatch(EventInterface $event): void
    {
        $eventName = $event->getName();

        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            $listener($event);
        }
    }

    /**
     * Forget all listeners for an event
     */
    public function forget(string $eventName): void
    {
        unset($this->listeners[$eventName]);
    }

    /**
     * Forget all listeners
     */
    public function flush(): void
    {
        $this->listeners = [];
    }

    /**
     * Get all listeners for an event
     */
    public function getListeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }

    /**
     * Check if event has listeners
     */
    public function hasListeners(string $eventName): bool
    {
        return isset($this->listeners[$eventName]) && count($this->listeners[$eventName]) > 0;
    }
}
