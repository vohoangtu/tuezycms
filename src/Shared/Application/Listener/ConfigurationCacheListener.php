<?php

declare(strict_types=1);

namespace Shared\Application\Listener;

use Shared\Domain\Event\ConfigurationToggledEvent;
use Shared\Domain\Event\ConfigurationUpdatedEvent;
use Shared\Infrastructure\Cache\Cache;

/**
 * Configuration Cache Listener
 * Clears configuration cache when config is toggled or updated
 */
class ConfigurationCacheListener
{
    public function handleConfigurationToggled(ConfigurationToggledEvent $event): void
    {
        $this->clearCache($event->getConfigId(), $event->getConfigName());
        error_log("Configuration {$event->getConfigName()} toggled to " . ($event->isEnabled() ? 'enabled' : 'disabled'));
    }

    public function handleConfigurationUpdated(ConfigurationUpdatedEvent $event): void
    {
        $this->clearCache($event->getConfigId(), $event->getConfigName());
        error_log("Configuration {$event->getConfigName()} updated");
    }

    private function clearCache(int $configId, string $configName): void
    {
        // Clear specific config cache
        Cache::delete("config:{$configId}");
        Cache::delete("config:name:{$configName}");
        
        // Clear configurations list cache
        Cache::delete('configurations:all');
        
        error_log("Cache cleared for configuration: {$configName} (ID: {$configId})");
    }
}
