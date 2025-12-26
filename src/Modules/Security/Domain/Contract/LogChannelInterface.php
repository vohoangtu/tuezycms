<?php

declare(strict_types=1);

namespace Modules\Security\Domain\Contract;

use Modules\Security\Domain\Entity\SecurityLog;

interface LogChannelInterface
{
    public function write(SecurityLog $log): void;
}
