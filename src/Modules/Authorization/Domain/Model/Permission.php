<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Model;

use DateTimeImmutable;

/**
 * Permission Domain Model
 * Represents a single permission in the system
 */
class Permission
{
    private ?int $id = null;
    private string $name;
    private string $displayName;
    private ?string $description;
    private string $resource;
    private string $action;
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        string $displayName,
        string $resource,
        string $action,
        ?string $description = null
    ) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->resource = $resource;
        $this->action = $action;
        $this->description = $description;
        $this->createdAt = new DateTimeImmutable();
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Check if this permission matches a permission string
     *
     * @param string $permissionString Permission string (e.g., 'articles.create')
     * @return bool
     */
    public function matches(string $permissionString): bool
    {
        return $this->name === $permissionString;
    }

    /**
     * Check if this permission matches a resource and action
     *
     * @param string $resource
     * @param string $action
     * @return bool
     */
    public function matchesResourceAction(string $resource, string $action): bool
    {
        return $this->resource === $resource && $this->action === $action;
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
            'resource' => $this->resource,
            'action' => $this->action,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
}
