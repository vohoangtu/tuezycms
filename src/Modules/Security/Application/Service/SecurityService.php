<?php

declare(strict_types=1);

namespace Modules\Security\Application\Service;

use Modules\Security\Domain\Contract\LoggerInterface;
use Modules\Security\Domain\Entity\BlockedIp;
use Modules\Security\Domain\Entity\SecurityLog;
use Modules\Security\Domain\Repository\BlockedIpRepositoryInterface;
use Modules\Security\Domain\Repository\SecurityLogRepositoryInterface;
use Shared\Infrastructure\Security\KeyValidator;

class SecurityService
{
    private SecurityLogRepositoryInterface $logRepository;
    private BlockedIpRepositoryInterface $blockedIpRepository;
    private KeyValidator $keyValidator;
    private ?LoggerInterface $logger;
    private ?FileIntegrityScanner $scanner;

    public function __construct(
        SecurityLogRepositoryInterface $logRepository,
        BlockedIpRepositoryInterface $blockedIpRepository,
        KeyValidator $keyValidator,
        ?LoggerInterface $logger = null,
        ?FileIntegrityScanner $scanner = null
    ) {
        $this->logRepository = $logRepository;
        $this->blockedIpRepository = $blockedIpRepository;
        $this->keyValidator = $keyValidator;
        $this->logger = $logger;
        $this->scanner = $scanner;
    }

    // ... existing methods ...

    /**
     * Check system integrity using detailed scanner if available, else fallback
     */
    public function checkIntegrity(): bool
    {
        if ($this->scanner) {
            $result = $this->scanner->verify();
            $isSecure = $result['status'] === 'clean';
        } else {
            $isSecure = $this->keyValidator->validateSourceIntegrity();
        }

        if (!$isSecure) {
            $this->log(
                'security.integrity_fail',
                $_SERVER['REMOTE_ADDR'] ?? 'system',
                'Source code integrity check failed',
                null,
                null,
                [],
                'critical'
            );
        }
        return $isSecure;
    }

    /**
     * Run detailed scan
     */
    public function runIntegrityScan(): array
    {
        if (!$this->scanner) {
            throw new \RuntimeException("File Integrity Scanner not configured.");
        }
        return $this->scanner->verify();
    }

    /**
     * Approve current files (Update Manifest)
     */
    public function approveIntegrityChanges(): int
    {
        if (!$this->scanner) {
            throw new \RuntimeException("File Integrity Scanner not configured.");
        }
        
        $count = $this->scanner->generateManifest();
        
        $this->log(
            'security.integrity_approve',
            $_SERVER['REMOTE_ADDR'] ?? 'system',
            "Integrity manifest updated. Files scanned: $count",
            null,
            null,
            [],
            'warning'
        );
        
        return $count;
    }

    public function log(
        string $action,
        string $ipAddress,
        ?string $description = null,
        ?int $userId = null,
        ?string $userAgent = null,
        array $context = [],
        string $level = 'info'
    ): void {
        if ($this->logger) {
            $this->logger->log(
                $action,
                $ipAddress,
                $level,
                $description,
                $userId,
                $userAgent,
                $context
            );
        } else {
            // Fallback to repository if no logger configured (backward compat)
            $log = new SecurityLog(
                $action,
                $ipAddress,
                $userId,
                $description,
                $userAgent,
                $context,
                $level
            );
            $this->logRepository->save($log);
        }
    }

    public function blockIp(string $ip, string $reason, ?int $blockedBy = null, ?int $durationMinutes = null): void
    {
        $existing = $this->blockedIpRepository->findByIp($ip);
        if ($existing) {
            // Already explicitly added, maybe update expire?
            // For now, if active, do nothing or re-enable
            if (!$existing->isActive()) {
                $this->blockedIpRepository->unblock($ip); // Actually implementing unblock usually sets IsActive=0. 
                // To re-block, we need to set IsActive=1.
                // My repository unblock sets 0. 
                // Let's create new BlockedIp or update existing.
                // Repository 'save' handles update.
                $existing->setIsActive(true);
                $this->blockedIpRepository->save($existing);
            }
            return;
        }

        $expiresAt = null;
        if ($durationMinutes) {
            $expiresAt = (new \DateTimeImmutable())->modify("+{$durationMinutes} minutes");
        }

        $blockedIp = new BlockedIp($ip, $reason, $blockedBy, $expiresAt);
        $this->blockedIpRepository->save($blockedIp);

        // Log the blocking action
        $this->log(
            'security.ip_block',
            $ip,
            "IP blocked: $reason",
            $blockedBy,
            null,
            ['duration' => $durationMinutes],
            'warning'
        );
    }

    public function unblockIp(string $ip, ?int $unblockedBy = null): void
    {
        $this->blockedIpRepository->unblock($ip);
        
        $this->log(
            'security.ip_unblock',
            $ip, // IP of the user performing action usually, but here param is target IP.
                 // Ideally we should log WHO did it. But method signature restriction.
                 // In real usage, caller passes generic IP or context.
            "IP unblocked",
            $unblockedBy,
            null,
            ['target_ip' => $ip],
            'info'
        );
    }

    public function isIpBlocked(string $ip): bool
    {
        return $this->blockedIpRepository->isBlocked($ip);
    }

    public function getRecentLogs(int $limit = 50): array
    {
        return $this->logRepository->findAll([], $limit);
    }

    public function getBlockedIps(int $limit = 50): array
    {
        return $this->blockedIpRepository->findAll($limit);
    }

    /**
     * Count recent authentication failures for an IP
     */
    public function countRecentAuthFailures(string $ip, int $minutes = 15): int
    {
        $fromDate = (new \DateTimeImmutable("-{$minutes} minutes"))->format('Y-m-d H:i:s');
        
        return $this->logRepository->count([
            'action' => 'auth.login_fail',
            'ip_address' => $ip,
            'from_date' => $fromDate
        ]);
    }


}
