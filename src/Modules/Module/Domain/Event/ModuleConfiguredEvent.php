<?php

declare(strict_types=1);

namespace Modules\Module\Domain\Event;

use Shared\Domain\Event\Event;

/**
 * Module Configured Event
 * Fired when a module's configuration is updated
 */
class ModuleConfiguredEvent extends Event
{
    public function __construct(
        private int $moduleId,
        private string $moduleName,
        private array $config
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'module.configured';
    }

    public function getPayload(): array
    {
        return [
            'module_id' => $this->moduleId,
            'module_name' => $this->moduleName,
            'config' => $this->config,
        ];
    }

    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
