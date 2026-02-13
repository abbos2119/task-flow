<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Data;

class SendOtpResponseData extends Data
{
    public function __construct(
        public string $message,
    ) {}
}
