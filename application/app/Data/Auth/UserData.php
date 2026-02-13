<?php

declare(strict_types=1);

namespace App\Data\Auth;

use App\Models\User;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public int $id,
        public ?string $email,
        public ?string $login,
        public ?string $phone,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            email: $user->email,
            login: $user->login,
            phone: $user->phone,
        );
    }
}
