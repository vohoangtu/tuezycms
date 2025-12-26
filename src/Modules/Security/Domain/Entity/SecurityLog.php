<?php

declare(strict_types=1);

namespace Modules\Security\Domain\Entity;

class SecurityLog
{
    private ?int $id;
    private ?int $userId;
    private string $action;
    private ?string $description;
    private string $ipAddress;
    private ?string $userAgent;
    private ?array $context;
    private string $level;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $action,
        string $ipAddress,
        ?int $userId = null,
        ?string $description = null,
        ?string $userAgent = null,
        ?array $context = [],
        string $level = 'info'
    ) {
        $this->action = $action;
        $this->ipAddress = $ipAddress;
        $this->userId = $userId;
        $this->description = $description;
        $this->userAgent = $userAgent;
        $this->context = $context;
        $this->level = $level;
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->userId; }
    public function getAction(): string { return $this->action; }
    public function getDescription(): ?string { return $this->description; }
    public function getIpAddress(): string { return $this->ipAddress; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function getContext(): ?array { return $this->context; }
    public function getLevel(): string { return $this->level; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    // Setters (for hydration)
    public function setId(int $id): void { $this->id = $id; }
    public function setCreatedAt(\DateTimeImmutable $date): void { $this->createdAt = $date; }
}
