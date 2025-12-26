<?php

declare(strict_types=1);

namespace Modules\Security\Domain\Repository;

use Modules\Security\Domain\Entity\BlockedIp;

interface BlockedIpRepositoryInterface
{
    public function save(BlockedIp $blockedIp): void;
    public function findByIp(string $ip): ?BlockedIp;
    public function unblock(string $ip): void;
    public function findAll(int $limit = 50, int $offset = 0): array;
    public function isBlocked(string $ip): bool;
}
