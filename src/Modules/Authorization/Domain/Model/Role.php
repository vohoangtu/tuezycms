<?php

declare(strict_types=1);

namespace Modules\Authorization\Domain\Model;

use DateTimeImmutable;

/**
 * Role Domain Model
 * Represents a role with associated permissions
 */
class Role
{
    private ?int $id = null;
    private string $name;
    private string $displayName;
    private ?string $description;
    private bool $isSystem = false;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    
    /** @var Permission[] */
    private array $permissions = [];

    public function __construct(
        string $name,
        string $displayName,
        ?string $description = null,
        bool $isSystem = false
    ) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->isSystem = $isSystem;
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

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
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

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): void
    {
        $this->isSystem = $isSystem;
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
     * Get all permissions
     *
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Set permissions
     *
     * @param Permission[] $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * Add a permission to this role
     *
     * @param Permission $permission
     */
    public function addPermission(Permission $permission): void
    {
        $permissionId = $permission->getId();
        
        // Check if permission already exists
        foreach ($this->permissions as $existingPermission) {
            if ($existingPermission->getId() === $permissionId) {
                return;
            }
        }
        
        $this->permissions[] = $permission;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Remove a permission from this role
     *
     * @param Permission $permission
     */
    public function removePermission(Permission $permission): void
    {
        $permissionId = $permission->getId();
        
        $this->permissions = array_filter(
            $this->permissions,
            fn($p) => $p->getId() !== $permissionId
        );
        
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Check if role has a specific permission
     *
     * @param string $permissionName Permission name (e.g., 'articles.create')
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->matches($permissionName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if role has permission for resource and action
     *
     * @param string $resource
     * @param string $action
     * @return bool
     */
    public function hasPermissionFor(string $resource, string $action): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->matchesResourceAction($resource, $action)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get permission names
     *
     * @return string[]
     */
    public function getPermissionNames(): array
    {
        return array_map(
            fn(Permission $p) => $p->getName(),
            $this->permissions
        );
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
            'is_system' => $this->isSystem,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'permissions' => array_map(fn($p) => $p->toArray(), $this->permissions)
        ];
    }
}
