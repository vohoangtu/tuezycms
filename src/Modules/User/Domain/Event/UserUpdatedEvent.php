<?php

declare(strict_types=1);

namespace Modules\User\Domain\Event;

use Shared\Domain\Event\Event;
use Modules\User\Domain\Model\User;

/**
 * User Updated Event
 * Fired when a user is updated
 */
class UserUpdatedEvent extends Event
{
    public function __construct(
        private User $user,
        private array $changes = []
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'user.updated';
    }

    public function getPayload(): array
    {
        return [
            'user_id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'changes' => $this->changes,
        ];
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }
}
