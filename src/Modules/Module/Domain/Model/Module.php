<?php

declare(strict_types=1);

namespace Modules\Module\Domain\Model;

use DateTimeImmutable;

/**
 * Module Domain Model
 * Represents a system module that can be enabled/disabled
 */
class Module
{
    private ?int $id = null;
    private string $name;
    private string $displayName;
    private ?string $description;
    private ?string $icon;
    private string $category;
    private bool $isEnabled = false;
    private bool $isSystem = false;
    private array $config = [];
    private string $version;
    private int $sortOrder = 0;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        string $displayName,
        ?string $description = null,
        ?string $icon = null,
        string $category = 'general',
        bool $isEnabled = false,
        bool $isSystem = false,
        array $config = [],
        string $version = '1.0.0',
        int $sortOrder = 0
    ) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->icon = $icon;
        $this->category = $category;
        $this->isEnabled = $isEnabled;
        $this->isSystem = $isSystem;
        $this->config = $config;
        $this->version = $version;
        $this->sortOrder = $sortOrder;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function enable(): void
    {
        if ($this->isEnabled) {
            return;
        }

        $this->isEnabled = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function disable(): void
    {
        if ($this->isSystem) {
            throw new \RuntimeException("Cannot disable system module: {$this->name}");
        }

        $this->isEnabled = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function setConfigValue(string $key, $value): void
    {
        $this->config[$key] = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->displayName,
            'description' => $this->description,
            'icon' => $this->icon,
            'category' => $this->category,
            'is_enabled' => $this->isEnabled,
            'is_system' => $this->isSystem,
            'config' => $this->config,
            'version' => $this->version,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
