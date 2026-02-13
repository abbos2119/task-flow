<?php

declare(strict_types=1);

namespace App\Data\TaskFlow;

use App\Constants\RoleNames;
use App\Models\User;

readonly class TaskFlowScope
{
    public function __construct(
        public string $userId,
        public array $permissions,
        public bool $isManager,
        public bool $mine,
    ) {}

    public static function fromUser(User $user, bool $mine): self
    {
        return new self(
            userId: (string) $user->id,
            permissions: $user->getAllPermissions()->pluck('name')->toArray(),
            isManager: $user->hasRole(RoleNames::MANAGER),
            mine: $mine,
        );
    }
}
