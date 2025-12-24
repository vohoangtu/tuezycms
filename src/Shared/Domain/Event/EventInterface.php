<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

/**
 * Event Interface
 * Base interface for all domain events
 */
interface EventInterface
{
    /**
     * Get event name
     */
    public function getName(): string;

    /**
     * Get when event occurred
     */
    public function getOccurredOn(): \DateTimeImmutable;

    /**
     * Get event payload
     */
    public function getPayload(): array;
}
