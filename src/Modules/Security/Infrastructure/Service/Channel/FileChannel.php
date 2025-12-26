<?php

declare(strict_types=1);

namespace Modules\Security\Infrastructure\Service\Channel;

use Modules\Security\Domain\Contract\LogChannelInterface;
use Modules\Security\Domain\Entity\SecurityLog;

class FileChannel implements LogChannelInterface
{
    private string $logPath;

    public function __construct(string $logPath = null)
    {
        // Default to storage/logs/security.log
        $this->logPath = $logPath ?? __DIR__ . '/../../../../../../storage/logs';
        
        // Ensure directory exists
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function write(SecurityLog $log): void
    {
        $date = new \DateTimeImmutable();
        $filename = $this->logPath . '/security-' . $date->format('Y-m-d') . '.log';
        
        $entry = sprintf(
            "[%s] %s | %s | %s | %s | %s | %s | %s" . PHP_EOL,
            $log->getCreatedAt()->format('Y-m-d H:i:s'),
            strtoupper($log->getLevel()),
            $log->getAction(),
            $log->getIpAddress(),
            $log->getUserId() ?? 'Guest',
            $log->getDescription(),
            $log->getUserAgent() ?? '-',
            json_encode($log->getContext() ?? [])
        );

        file_put_contents($filename, $entry, FILE_APPEND);
    }
}
