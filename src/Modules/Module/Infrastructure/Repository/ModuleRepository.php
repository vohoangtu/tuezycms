<?php

declare(strict_types=1);

namespace Modules\Module\Infrastructure\Repository;

use Modules\Module\Domain\Model\Module;
use Shared\Infrastructure\Database\DB;
use Shared\Infrastructure\Cache\Cache;
use DateTimeImmutable;

class ModuleRepository
{
    /**
     * Find module by ID
     */
    public function findById(int $id): ?Module
    {
        return Cache::remember("module:{$id}", 3600, function() use ($id) {
            $data = DB::table('modules')->find($id);
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    /**
     * Find module by name
     */
    public function findByName(string $name): ?Module
    {
        return Cache::remember("module:name:{$name}", 3600, function() use ($name) {
            $data = DB::table('modules')->where('name', '=', $name)->first();
            return $data ? $this->mapToEntity($data) : null;
        });
    }

    /**
     * Find all modules
     */
    public function findAll(): array
    {
        return Cache::remember('modules:all', 600, function() {
            $results = DB::table('modules')->orderBy('sort_order')->orderBy('name')->get();
            return array_map([$this, 'mapToEntity'], $results);
        });
    }

    /**
     * Find enabled modules only
     */
    public function findEnabled(): array
    {
        return Cache::remember('modules:enabled', 600, function() {
            $results = DB::table('modules')
                ->where('is_enabled', '=', 1)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
            return array_map([$this, 'mapToEntity'], $results);
        });
    }

    /**
     * Find modules by category
     */
    public function findByCategory(string $category): array
    {
        return Cache::remember("modules:category:{$category}", 600, function() use ($category) {
            $results = DB::table('modules')
                ->where('category', '=', $category)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
            return array_map([$this, 'mapToEntity'], $results);
        });
    }

    /**
     * Save module (insert or update)
     */
    public function save(Module $module): void
    {
        if ($module->getId() === null) {
            $this->insert($module);
        } else {
            $this->update($module);
        }
    }

    /**
     * Insert new module
     */
    private function insert(Module $module): void
    {
        $id = DB::table('modules')->insert([
            'name' => $module->getName(),
            'display_name' => $module->getDisplayName(),
            'description' => $module->getDescription(),
            'icon' => $module->getIcon(),
            'category' => $module->getCategory(),
            'is_enabled' => $module->isEnabled() ? 1 : 0,
            'is_system' => $module->isSystem() ? 1 : 0,
            'config' => json_encode($module->getConfig()),
            'version' => $module->getVersion(),
            'sort_order' => $module->getSortOrder(),
            'created_at' => $module->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $module->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $module->setId($id);
        $this->clearCache();
    }

    /**
     * Update existing module
     */
    private function update(Module $module): void
    {
        DB::table('modules')
            ->where('id', '=', $module->getId())
            ->update([
                'display_name' => $module->getDisplayName(),
                'description' => $module->getDescription(),
                'icon' => $module->getIcon(),
                'category' => $module->getCategory(),
                'is_enabled' => $module->isEnabled() ? 1 : 0,
                'config' => json_encode($module->getConfig()),
                'version' => $module->getVersion(),
                'sort_order' => $module->getSortOrder(),
                'updated_at' => $module->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);

        $this->clearCache();
    }

    /**
     * Delete module
     */
    public function delete(int $id): bool
    {
        // Check if module is system module
        $module = $this->findById($id);
        if ($module && $module->isSystem()) {
            throw new \RuntimeException("Cannot delete system module");
        }

        $result = DB::table('modules')->where('id', '=', $id)->delete();
        $this->clearCache();

        return $result > 0;
    }

    /**
     * Enable a module
     */
    public function enable(int $id): void
    {
        DB::table('modules')
            ->where('id', '=', $id)
            ->update([
                'is_enabled' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->clearCache();
    }

    /**
     * Disable a module
     */
    public function disable(int $id): void
    {
        // Check if module is system module
        $module = $this->findById($id);
        if ($module && $module->isSystem()) {
            throw new \RuntimeException("Cannot disable system module");
        }

        DB::table('modules')
            ->where('id', '=', $id)
            ->update([
                'is_enabled' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->clearCache();
    }

    /**
     * Update module config
     */
    public function updateConfig(int $id, array $config): void
    {
        DB::table('modules')
            ->where('id', '=', $id)
            ->update([
                'config' => json_encode($config),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->clearCache();
    }

    /**
     * Map database row to Module entity
     */
    private function mapToEntity($data): Module
    {
        // Convert array to object if needed (DB facade returns arrays)
        if (is_array($data)) {
            $data = (object) $data;
        }
        
        $module = new Module(
            $data->name,
            $data->display_name,
            $data->description,
            $data->icon,
            $data->category,
            (bool) $data->is_enabled,
            (bool) $data->is_system,
            json_decode($data->config ?? '{}', true) ?? [],
            $data->version,
            (int) $data->sort_order
        );

        $module->setId($data->id);
        $module->setCreatedAt(new DateTimeImmutable($data->created_at));
        $module->setUpdatedAt(new DateTimeImmutable($data->updated_at));

        return $module;
    }

    /**
     * Clear all module-related cache
     */
    private function clearCache(): void
    {
        Cache::delete('modules:all');
        Cache::delete('modules:enabled');
        // Delete category caches
        $categories = ['user', 'product', 'content', 'system', 'general'];
        foreach ($categories as $category) {
            Cache::delete("modules:category:{$category}");
        }
    }
}
