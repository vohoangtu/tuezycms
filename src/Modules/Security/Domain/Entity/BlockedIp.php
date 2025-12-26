<?php

declare(strict_types=1);

namespace Modules\Security\Domain\Entity;

class BlockedIp
{
    private ?int $id;
    private string $ipAddress;
    private ?string $reason;
    private ?int $blockedBy;
    private ?\DateTimeImmutable $expiresAt;
    private bool $isActive;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $ipAddress,
        ?string $reason = null,
        ?int $blockedBy = null,
        ?\DateTimeImmutable $expiresAt = null
    ) {
        $this->ipAddress = $ipAddress;
        $this->reason = $reason;
        $this->blockedBy = $blockedBy;
        $this->expiresAt = $expiresAt;
        $this->isActive = true;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }
        return $this->expiresAt < new \DateTimeImmutable();
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function getIpAddress(): string { return $this->ipAddress; }
    public function getReason(): ?string { return $this->reason; }
    public function getBlockedBy(): ?int { return $this->blockedBy; }
    public function getExpiresAt(): ?\DateTimeImmutable { return $this->expiresAt; }
    public function isActive(): bool { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    
    public function setId(int $id): void { $this->id = $id; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
    public function setCreatedAt(\DateTimeImmutable $date): void { $this->createdAt = $date; }
    public function setExpiresAt(?\DateTimeImmutable $date): void { $this->expiresAt = $date; }
}
