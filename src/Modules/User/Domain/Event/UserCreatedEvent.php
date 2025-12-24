<?php

declare(strict_types=1);

namespace Modules\User\Domain\Event;

use Shared\Domain\Event\Event;
use Modules\User\Domain\Model\User;

/**
 * User Created Event
 * Fired when a new user is created
 */
class UserCreatedEvent extends Event
{
    public function __construct(
        private User $user
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'user.created';
    }

    public function getPayload(): array
    {
        return [
            'user_id' => $this->user->getId(),
            'email' => $this->user->getEmail(),
            'full_name' => $this->user->getFullName(),
        ];
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
