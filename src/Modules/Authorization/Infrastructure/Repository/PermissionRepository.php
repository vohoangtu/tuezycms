<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Repository;

use Modules\Authorization\Domain\Model\Permission;
use Shared\Infrastructure\Database\DB;
use Shared\Infrastructure\Cache\Cache;

class PermissionRepository
{
    // Using DB facade and Cache

    /**
     * Find permission by ID
     */
    public function findById(int $id): ?Permission
    {
        return Cache::remember("permission:{$id}", 3600, function() use ($id) {
            $data = DB::table('permissions')->find($id);
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    /**
     * Find permission by name
     */
    public function findByName(string $name): ?Permission
    {
        return Cache::remember("permission:name:{$name}", 3600, function() use ($name) {
            $data = DB::table('permissions')->where('name', '=', $name)->first();
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    /**
     * Find all permissions
     */
    public function findAll(): array
    {
        return Cache::remember('permissions:all', 600, function() {
            $results = DB::table('permissions')
                ->orderBy('resource')
                ->orderBy('action')
                ->get();
            return array_map([$this, 'mapToEntity'], $results);
        });
    }

    /**
     * Find permissions by resource
     */
    public function findByResource(string $resource): array
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE resource = :resource ORDER BY action");
        $stmt->execute([':resource' => $resource]);
        
        $permissions = [];
        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $permissions[] = $this->mapToEntity($data);
        }
        
        return $permissions;
    }

    /**
     * Save permission (insert or update)
     */
    public function save(Permission $permission): void
    {
        if ($permission->getId() === null) {
            $this->insert($permission);
        } else {
            $this->update($permission);
        }
    }

    /**
     * Insert new permission
     */
    private function insert(Permission $permission): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO permissions (
                name, display_name, resource, action, description, created_at
            ) VALUES (
                :name, :display_name, :resource, :action, :description, :created_at
            )
        ");

        $stmt->execute([
            ':name' => $permission->getName(),
            ':display_name' => $permission->getDisplayName(),
            ':resource' => $permission->getResource(),
            ':action' => $permission->getAction(),
            ':description' => $permission->getDescription(),
            ':created_at' => $permission->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        $permission->setId((int)$this->db->lastInsertId());
    }

    /**
     * Update existing permission
     */
    private function update(Permission $permission): void
    {
        $stmt = $this->db->prepare("
            UPDATE permissions SET
                name = :name,
                display_name = :display_name,
                resource = :resource,
                action = :action,
                description = :description
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $permission->getId(),
            ':name' => $permission->getName(),
            ':display_name' => $permission->getDisplayName(),
            ':resource' => $permission->getResource(),
            ':action' => $permission->getAction(),
            ':description' => $permission->getDescription(),
        ]);
    }

    /**
     * Delete permission
     */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM permissions WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Map database row to Permission entity
     */
    private function mapToEntity(array $data): Permission
    {
        $permission = new Permission(
            $data['name'],
            $data['display_name'],
            $data['resource'],
            $data['action'],
            $data['description']
        );

        $permission->setId((int)$data['id']);
        $permission->setCreatedAt(new \DateTimeImmutable($data['created_at']));

        return $permission;
    }
}
