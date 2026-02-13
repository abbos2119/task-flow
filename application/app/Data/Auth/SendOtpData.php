<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

class SendOtpData extends Data
{
    public function __construct(
        public string $email,
    ) {}
}
