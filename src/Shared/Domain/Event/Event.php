<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

/**
 * Base Event Class
 * Abstract base for all domain events
 */
abstract class Event implements EventInterface
{
    private \DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new \DateTimeImmutable();
    }

    /**
     * Get when event occurred
     */
    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * Get event name (must be implemented by child classes)
     */
    abstract public function getName(): string;

    /**
     * Get event payload (must be implemented by child classes)
     */
    abstract public function getPayload(): array;
}
