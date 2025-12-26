<?php

declare(strict_types=1);

namespace Modules\Security\Infrastructure\Service;

use Modules\Security\Domain\Contract\LogChannelInterface;
use Modules\Security\Domain\Contract\LoggerInterface;
use Modules\Security\Domain\Entity\SecurityLog;

class LogManager implements LoggerInterface
{
    /** @var LogChannelInterface[] */
    private array $channels = [];

    public function addChannel(LogChannelInterface $channel): void
    {
        $this->channels[] = $channel;
    }

    public function log(
        string $action,
        string $ipAddress,
        string $level = 'info',
        ?string $description = null,
        ?int $userId = null,
        ?string $userAgent = null,
        array $context = []
    ): void {
        $log = new SecurityLog(
            $action,
            $ipAddress,
            $userId,
            $description,
            $userAgent,
            $context,
            $level
        );

        foreach ($this->channels as $channel) {
            try {
                $channel->write($log);
            } catch (\Throwable $e) {
                // Prevent logging failure from crashing the app
                error_log("Failed to write to security log channel: " . $e->getMessage());
            }
        }
    }

    public function info(string $action, string $ipAddress, ?string $description = null, array $context = []): void
    {
        $this->log($action, $ipAddress, 'info', $description, null, null, $context);
    }

    public function warning(string $action, string $ipAddress, ?string $description = null, array $context = []): void
    {
        $this->log($action, $ipAddress, 'warning', $description, null, null, $context);
    }

    public function critical(string $action, string $ipAddress, ?string $description = null, array $context = []): void
    {
        $this->log($action, $ipAddress, 'critical', $description, null, null, $context);
    }
}
