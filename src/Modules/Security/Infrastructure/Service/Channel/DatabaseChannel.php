<?php

declare(strict_types=1);

namespace Modules\Security\Infrastructure\Service\Channel;

use Modules\Security\Domain\Contract\LogChannelInterface;
use Modules\Security\Domain\Entity\SecurityLog;
use Modules\Security\Domain\Repository\SecurityLogRepositoryInterface;

class DatabaseChannel implements LogChannelInterface
{
    private SecurityLogRepositoryInterface $repository;

    public function __construct(SecurityLogRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function write(SecurityLog $log): void
    {
        // Repository handles persistence
        $this->repository->save($log);
    }
}
