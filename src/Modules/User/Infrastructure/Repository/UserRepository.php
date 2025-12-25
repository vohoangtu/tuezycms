<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Repository;

use Modules\User\Domain\Model\User;
use Modules\User\Domain\Event\UserCreatedEvent;
use Modules\User\Domain\Event\UserDeletedEvent;
use Shared\Infrastructure\Database\DB;
use Shared\Infrastructure\Cache\Cache;

class UserRepository
{
    // No need for PDO instance anymore, using DB facade

    public function save(User $user): void
    {
        if ($user->getId() === null) {
            $this->insert($user);
        } else {
            $this->update($user);
        }
    }

    private function insert(User $user): void
    {
        $id = DB::table('users')->insert([
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'full_name' => $user->getFullName(),
            'role' => $user->getRole(),
            'is_active' => $user->isActive() ? 1 : 0,
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $user->setId($id);
        
        // Dispatch event
        event(new UserCreatedEvent($user));
    }

    private function update(User $user): void
    {
        DB::table('users')
            ->where('id', '=', $user->getId())
            ->update([
                'email' => $user->getEmail(),
                'password_hash' => $user->getPasswordHash(),
                'full_name' => $user->getFullName(),
                'role' => $user->getRole(),
                'is_active' => $user->isActive() ? 1 : 0,
                'last_login_at' => $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
                'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);
        
        // Clear cache
        Cache::delete("user:{$user->getId()}");
        Cache::delete('users:all');
    }

    public function findById(int $id): ?User
    {
        return Cache::remember("user:{$id}", 3600, function() use ($id) {
            $data = DB::table('users')->find($id);
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    public function findByEmail(string $email): ?User
    {
        return Cache::remember("user:email:{$email}", 3600, function() use ($email) {
            $data = DB::table('users')
                ->where('email', '=', $email)
                ->first();
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    private function mapToEntity(array $data): User
    {
        $user = new User(
            $data['email'],
            $data['password_hash'],
            $data['full_name'],
            $data['role']
        );

        $user->setId($data['id']);
        $user->setIsActive((bool)$data['is_active']);
        
        if ($data['last_login_at']) {
            $user->setLastLoginAt(new \DateTimeImmutable($data['last_login_at']));
        }

        return $user;
    }

    /**
     * Get roles for a user
     *
     * @param int $userId
     * @return array Array of role data
     */
    public function getRoles(int $userId): array
    {
        return Cache::remember("user:{$userId}:roles", 3600, function() use ($userId) {
            return DB::table('roles as r')
                ->join('user_roles as ur', 'r.id', '=', 'ur.role_id')
                ->where('ur.user_id', '=', $userId)
                ->select('r.*')
                ->orderBy('r.name')
                ->get();
        });
    }

    /**
     * Sync roles for a user
     *
     * @param int $userId
     * @param int[] $roleIds
     * @return void
     */
    public function syncRoles(int $userId, array $roleIds): void
    {
        DB::transaction(function() use ($userId, $roleIds) {
            // Delete existing roles
            DB::table('user_roles')
                ->where('user_id', '=', $userId)
                ->delete();
            
            // Insert new roles
            foreach ($roleIds as $roleId) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]);
            }
        });
        
        // Clear cache
        Cache::delete("user:{$userId}:roles");
        Cache::delete("user:{$userId}:permissions");
    }

    /**
     * Get all permissions for a user (from all their roles)
     *
     * @param int $userId
     * @return array Array of permission data
     */
    public function getPermissions(int $userId): array
    {
        return Cache::remember("user:{$userId}:permissions", 3600, function() use ($userId) {
            return DB::raw("
                SELECT DISTINCT p.* FROM permissions p
                INNER JOIN role_permissions rp ON p.id = rp.permission_id
                INNER JOIN user_roles ur ON rp.role_id = ur.role_id
                WHERE ur.user_id = ?
                ORDER BY p.resource, p.action
            ", [$userId]);
        });
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        DB::transaction(function() use ($id) {
            // Delete user roles first
            DB::table('user_roles')
                ->where('user_id', '=', $id)
                ->delete();
            
            // Delete user
            DB::table('users')
                ->where('id', '=', $id)
                ->delete();
        });
        
        // Clear cache
        Cache::delete("user:{$id}");
        Cache::delete("user:email:*"); // Clear all email-based cache
        Cache::delete('users:all');
        Cache::delete("user:{$id}:roles");
        Cache::delete("user:{$id}:permissions");
        
        // Dispatch event
        event(new UserDeletedEvent($id));
    }

    /**
     * Find all users
     *
     * @return User[]
     */
    public function findAll(): array
    {
        return Cache::remember('users:all', 3600, function() {
            $data = DB::table('users')
                ->orderBy('created_at', 'DESC')
                ->get();
            
            return array_map(fn($row) => $this->mapToEntity($row), $data);
        });
    }
}
