<?php

declare(strict_types=1);

namespace Modules\Module\Application\Service;

use Modules\Module\Infrastructure\Repository\ModuleRepository;
use Modules\Module\Domain\Model\Module;
use Shared\Infrastructure\Cache\Cache;

/**
 * Module Service
 * Business logic for module management
 */
class ModuleService
{
    private ModuleRepository $repository;

    public function __construct(ModuleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Check if a module is enabled
     */
    public function isModuleEnabled(string $moduleName): bool
    {
        return Cache::remember("module:enabled:{$moduleName}", 3600, function() use ($moduleName) {
            $module = $this->repository->findByName($moduleName);
            return $module ? $module->isEnabled() : false;
        });
    }

    /**
     * Get all enabled modules
     */
    public function getEnabledModules(): array
    {
        return $this->repository->findEnabled();
    }

    /**
     * Get all modules
     */
    public function getAllModules(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Get modules by category
     */
    public function getModulesByCategory(string $category): array
    {
        return $this->repository->findByCategory($category);
    }

    /**
     * Get modules grouped by category
     */
    public function getModulesGroupedByCategory(): array
    {
        $modules = $this->repository->findAll();
        $grouped = [];

        foreach ($modules as $module) {
            $category = $module->getCategory();
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $module;
        }

        return $grouped;
    }

    /**
     * Toggle module enabled status
     */
    public function toggleModule(string $moduleName, bool $enabled): void
    {
        $module = $this->repository->findByName($moduleName);
        
        if (!$module) {
            throw new \RuntimeException("Module not found: {$moduleName}");
        }

        if ($enabled) {
            $module->enable();
            $this->repository->enable($module->getId());
        } else {
            $module->disable();
            $this->repository->disable($module->getId());
        }

        // Clear cache
        Cache::delete("module:enabled:{$moduleName}");
        Cache::delete('modules:enabled');
    }

    /**
     * Enable a module
     */
    public function enableModule(string $moduleName): void
    {
        $this->toggleModule($moduleName, true);
    }

    /**
     * Disable a module
     */
    public function disableModule(string $moduleName): void
    {
        $this->toggleModule($moduleName, false);
    }

    /**
     * Update module configuration
     */
    public function updateModuleConfig(string $moduleName, array $config): void
    {
        $module = $this->repository->findByName($moduleName);
        
        if (!$module) {
            throw new \RuntimeException("Module not found: {$moduleName}");
        }

        $module->setConfig($config);
        $this->repository->updateConfig($module->getId(), $config);

        // Clear cache
        Cache::delete("module:{$module->getId()}");
        Cache::delete("module:name:{$moduleName}");
    }

    /**
     * Get module configuration
     */
    public function getModuleConfig(string $moduleName): array
    {
        $module = $this->repository->findByName($moduleName);
        return $module ? $module->getConfig() : [];
    }

    /**
     * Get a specific module config value
     */
    public function getModuleConfigValue(string $moduleName, string $key, $default = null)
    {
        $config = $this->getModuleConfig($moduleName);
        return $config[$key] ?? $default;
    }

    /**
     * Register a new module (used for module installation)
     */
    public function registerModule(array $data): Module
    {
        $module = new Module(
            $data['name'],
            $data['display_name'],
            $data['description'] ?? null,
            $data['icon'] ?? null,
            $data['category'] ?? 'general',
            $data['is_enabled'] ?? false,
            $data['is_system'] ?? false,
            $data['config'] ?? [],
            $data['version'] ?? '1.0.0',
            $data['sort_order'] ?? 0
        );

        $this->repository->save($module);

        // Clear cache
        Cache::delete('modules:all');
        Cache::delete('modules:enabled');

        return $module;
    }
}
