<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Model;

class MediaUsage
{
    private int $mediaId;
    private string $entityType;
    private ?int $entityId;
    private ?string $entityKey;
    private string $usageType;

    public function __construct(
        int $mediaId,
        string $entityType,
        string $usageType,
        ?int $entityId = null,
        ?string $entityKey = null
    ) {
        $this->mediaId = $mediaId;
        $this->entityType = $entityType;
        $this->usageType = $usageType;
        $this->entityId = $entityId;
        $this->entityKey = $entityKey;
    }

    public function getMediaId(): int
    {
        return $this->mediaId;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function getEntityKey(): ?string
    {
        return $this->entityKey;
    }

    public function getUsageType(): string
    {
        return $this->usageType;
    }
}

