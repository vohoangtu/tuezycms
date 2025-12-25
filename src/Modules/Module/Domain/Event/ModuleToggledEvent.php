<?php

declare(strict_types=1);

namespace Modules\Module\Domain\Event;

use Shared\Domain\Event\Event;

/**
 * Module Toggled Event
 * Fired when a module is enabled/disabled
 */
class ModuleToggledEvent extends Event
{
    public function __construct(
        private int $moduleId,
        private string $moduleName,
        private bool $isEnabled
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'module.toggled';
    }

    public function getPayload(): array
    {
        return [
            'module_id' => $this->moduleId,
            'module_name' => $this->moduleName,
            'is_enabled' => $this->isEnabled,
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

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
