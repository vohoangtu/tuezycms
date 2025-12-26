<?php

declare(strict_types=1);

namespace Modules\Security\Domain\Contract;

interface LoggerInterface
{
    public function log(
        string $action,
        string $ipAddress,
        string $level = 'info',
        ?string $description = null,
        ?int $userId = null,
        ?string $userAgent = null,
        array $context = []
    ): void;

    public function info(string $action, string $ipAddress, ?string $description = null, array $context = []): void;
    public function warning(string $action, string $ipAddress, ?string $description = null, array $context = []): void;
    public function critical(string $action, string $ipAddress, ?string $description = null, array $context = []): void;
}
