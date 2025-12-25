<?php

declare(strict_types=1);

namespace Shared\Domain\Event;

/**
 * Configuration Updated Event
 * Fired when a system configuration's value is updated
 */
class ConfigurationUpdatedEvent extends Event
{
    public function __construct(
        private int $configId,
        private string $configName,
        private array $config
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'configuration.updated';
    }

    public function getPayload(): array
    {
        return [
            'config_id' => $this->configId,
            'config_name' => $this->configName,
            'config' => $this->config,
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

    public function getConfig(): array
    {
        return $this->config;
    }
}
