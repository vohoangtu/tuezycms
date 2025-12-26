<?php

declare(strict_types=1);

namespace Modules\Security\Domain\Repository;

use Modules\Security\Domain\Entity\SecurityLog;

interface SecurityLogRepositoryInterface
{
    public function save(SecurityLog $log): void;
    
    /**
     * @param array $filters ['user_id', 'action', 'ip_address', 'level', 'from_date', 'to_date']
     * @param int $limit
     * @param int $offset
     * @return SecurityLog[]
     */
    public function findAll(array $filters = [], int $limit = 50, int $offset = 0): array;
    
    public function count(array $filters = []): int;
}
