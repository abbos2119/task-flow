<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

class LoginOtpData extends Data
{
    public function __construct(
        public string $identifier,
        public string $code,
    ) {}
}
