<?php

declare(strict_types=1);

namespace Modules\User\Domain\Event;

use Shared\Domain\Event\Event;

/**
 * User Deleted Event
 * Fired when a user is deleted
 */
class UserDeletedEvent extends Event
{
    public function __construct(
        private int $userId,
        private string $email
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return 'user.deleted';
    }

    public function getPayload(): array
    {
        return [
            'user_id' => $this->userId,
            'email' => $this->email,
        ];
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
