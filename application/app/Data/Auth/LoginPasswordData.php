<?php

declare(strict_types=1);

namespace App\Data\Auth;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class LoginPasswordData extends Data
{
    public function __construct(
        #[MapInputName('login_or_email')]
        public string $loginOrEmail,
        public string $password,
    ) {}
}
