<?php

declare(strict_types=1);

namespace Modules\Authorization\Infrastructure\Repository;

use Modules\Authorization\Domain\Model\Role;
use Shared\Infrastructure\Database\DB;
use Shared\Infrastructure\Cache\Cache;

class RoleRepository
{
    // Using DB facade and Cache

    /**
     * Find role by ID
     */
    public function findById(int $id): ?Role
    {
        return Cache::remember("role:{$id}", 3600, function() use ($id) {
            $data = DB::table('roles')->find($id);
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    /**
     * Find role by name
     */
    public function findByName(string $name): ?Role
    {
        return Cache::remember("role:name:{$name}", 3600, function() use ($name) {
            $data = DB::table('roles')->where('name', '=', $name)->first();
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    /**
     * Find all roles
     */
    public function findAll(): array
    {
        return Cache::remember('roles:all', 600, function() {
            $results = DB::table('roles')->orderBy('name')->get();
            return array_map([$this, 'mapToEntity'], $results);
        });
    }

    /**
     * Save role (insert or update)
     */
    public function save(Role $role): void
    {
        if ($role->getId() === null) {
            $this->insert($role);
        } else {
            $this->update($role);
        }
    }

    /**
     * Insert new role
     */
    private function insert(Role $role): void
    {
        $id = DB::table('roles')->insert([
            'name' => $role->getName(),
            'display_name' => $role->getDisplayName(),
            'description' => $role->getDescription(),
            'is_system' => $role->isSystem() ? 1 : 0,
            'created_at' => $role->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $role->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $role->setId($id);
        Cache::delete('roles:all');
    }

    /**
     * Update existing role
     */
    private function update(Role $role): void
    {
        DB::table('roles')
            ->where('id', '=', $role->getId())
            ->update([
                'name' => $role->getName(),
                'display_name' => $role->getDisplayName(),
                'description' => $role->getDescription(),
                'is_system' => $role->isSystem() ? 1 : 0,
                'updated_at' => $role->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);
        
        Cache::delete("role:{$role->getId()}");
        Cache::delete('roles:all');
    }

    /**
     * Delete role
     */
    public function delete(int $id): void
    {
        DB::table('roles')->where('id', '=', $id)->delete();
        
        Cache::delete("role:{$id}");
        Cache::delete('roles:all');
    }

    /**
     * Get permissions for a role
     */
    public function getPermissions(int $roleId): array
    {
        return Cache::remember("role:{$roleId}:permissions", 3600, function() use ($roleId) {
            return DB::table('permissions as p')
                ->join('role_permissions as rp', 'p.id', '=', 'rp.permission_id')
                ->where('rp.role_id', '=', $roleId)
                ->select('p.*')
                ->orderBy('p.resource')
                ->orderBy('p.action')
                ->get();
        });
    }

    /**
     * Sync permissions for a role
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        DB::transaction(function() use ($roleId, $permissionIds) {
            // Delete existing permissions
            DB::table('role_permissions')
                ->where('role_id', '=', $roleId)
                ->delete();
            
            // Insert new permissions
            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }
        });
        
        // Clear cache
        Cache::delete("role:{$roleId}:permissions");
    }

    /**
     * Map database row to Role entity
     */
    private function mapToEntity(array $data): Role
    {
        $role = new Role(
            $data['name'],
            $data['display_name'],
            $data['description'],
            (bool)$data['is_system']
        );

        $role->setId((int)$data['id']);
        $role->setCreatedAt(new \DateTimeImmutable($data['created_at']));
        $role->setUpdatedAt(new \DateTimeImmutable($data['updated_at']));

        return $role;
    }
}
