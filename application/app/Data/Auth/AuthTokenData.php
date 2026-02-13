<?php

declare(strict_types=1);

namespace App\Data\Auth;

use App\Models\User;
use Spatie\LaravelData\Data;

class AuthTokenData extends Data
{
    public function __construct(
        public string $token,
        public string $tokenType,
        public UserData $user,
    ) {}

    public static function fromUserAndToken(User $user, string $plainTextToken): self
    {
        return new self(
            token: $plainTextToken,
            tokenType: 'Bearer',
            user: UserData::fromModel($user),
        );
    }
}
