<?php

declare(strict_types=1);

namespace Modules\Module\Application\Listener;

use Modules\Module\Domain\Event\ModuleToggledEvent;
use Modules\Module\Domain\Event\ModuleConfiguredEvent;
use Shared\Infrastructure\Cache\Cache;

/**
 * Clear Module Cache Listener
 * Clears module cache when module is toggled or configured
 */
class ClearModuleCacheListener
{
    public function handleModuleToggled(ModuleToggledEvent $event): void
    {
        $this->clearCache($event->getModuleId(), $event->getModuleName());
        error_log("Module {$event->getModuleName()} toggled to " . ($event->isEnabled() ? 'enabled' : 'disabled'));
    }

    public function handleModuleConfigured(ModuleConfiguredEvent $event): void
    {
        $this->clearCache($event->getModuleId(), $event->getModuleName());
        error_log("Module {$event->getModuleName()} configuration updated");
    }

    private function clearCache(int $moduleId, string $moduleName): void
    {
        // Clear specific module cache
        Cache::delete("module:{$moduleId}");
        Cache::delete("module:name:{$moduleName}");
        
        // Clear modules list cache
        Cache::delete('modules:all');
        Cache::delete('modules:enabled');
        
        error_log("Cache cleared for module: {$moduleName} (ID: {$moduleId})");
    }
}
