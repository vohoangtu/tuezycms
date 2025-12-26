<?php

declare(strict_types=1);

namespace Modules\Security\Infrastructure\Repository;

use Modules\Security\Domain\Entity\BlockedIp;
use Modules\Security\Domain\Repository\BlockedIpRepositoryInterface;
use Shared\Infrastructure\Database\DatabaseConnection;

class DatabaseBlockedIpRepository implements BlockedIpRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function save(BlockedIp $blockedIp): void
    {
        if ($blockedIp->getId() === null) {
            $stmt = $this->db->prepare("
                INSERT INTO blocked_ips (
                    ip_address, reason, blocked_by, expires_at, is_active, created_at, updated_at
                ) VALUES (
                    :ip_address, :reason, :blocked_by, :expires_at, :is_active, :created_at, :updated_at
                )
            ");
        } else {
            $stmt = $this->db->prepare("
                UPDATE blocked_ips SET
                    ip_address = :ip_address,
                    reason = :reason,
                    blocked_by = :blocked_by,
                    expires_at = :expires_at,
                    is_active = :is_active,
                    updated_at = :updated_at
                WHERE id = :id
            ");
            $stmt->bindValue(':id', $blockedIp->getId(), \PDO::PARAM_INT);
        }

        $stmt->bindValue(':ip_address', $blockedIp->getIpAddress());
        $stmt->bindValue(':reason', $blockedIp->getReason());
        $stmt->bindValue(':blocked_by', $blockedIp->getBlockedBy(), \PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', $blockedIp->getExpiresAt()?->format('Y-m-d H:i:s'));
        $stmt->bindValue(':is_active', $blockedIp->isActive(), \PDO::PARAM_BOOL);
        $stmt->bindValue(':created_at', $blockedIp->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', (new \DateTimeImmutable())->format('Y-m-d H:i:s'));

        $stmt->execute();

        if ($blockedIp->getId() === null) {
            $blockedIp->setId((int)$this->db->lastInsertId());
        }
    }

    public function findByIp(string $ip): ?BlockedIp
    {
        $stmt = $this->db->prepare("SELECT * FROM blocked_ips WHERE ip_address = :ip LIMIT 1");
        $stmt->execute([':ip' => $ip]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapToEntity($row);
    }

    public function unblock(string $ip): void
    {
        $stmt = $this->db->prepare("UPDATE blocked_ips SET is_active = 0 WHERE ip_address = :ip");
        $stmt->execute([':ip' => $ip]);
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM blocked_ips ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $ips = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $ips[] = $this->mapToEntity($row);
        }

        return $ips;
    }

    public function isBlocked(string $ip): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM blocked_ips WHERE ip_address = :ip AND is_active = 1 LIMIT 1");
        $stmt->execute([':ip' => $ip]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $blockedIp = $this->mapToEntity($row);
        return !$blockedIp->isExpired();
    }

    private function mapToEntity(array $row): BlockedIp
    {
        $expiresAt = $row['expires_at'] ? new \DateTimeImmutable($row['expires_at']) : null;
        
        $blockedIp = new BlockedIp(
            $row['ip_address'],
            $row['reason'],
            $row['blocked_by'] ? (int)$row['blocked_by'] : null,
            $expiresAt
        );

        $blockedIp->setId((int)$row['id']);
        $blockedIp->setIsActive((bool)$row['is_active']);
        $blockedIp->setCreatedAt(new \DateTimeImmutable($row['created_at']));
        
        return $blockedIp;
    }
}
