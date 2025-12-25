<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

/**
 * Configuration Toggled Event
 * Fired when a system configuration is enabled/disabled
 */
class ConfigurationToggledEvent extends Event
{
    public function __construct(
        private int $configId,
        private string $configName,
        private bool $isEnabled
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'configuration.toggled';
    }

    public function getPayload(): array
    {
        return [
            'config_id' => $this->configId,
            'config_name' => $this->configName,
            'is_enabled' => $this->isEnabled,
        ];
    }

    public function getConfigId(): int
    {
        return $this->configId;
    }

    public function getConfigName(): string
    {
        return $this->configName;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
