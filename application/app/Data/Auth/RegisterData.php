<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public string $email,
        public string $login,
        public string $password,
    ) {}
}
