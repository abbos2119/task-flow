<?php

declare(strict_types=1);

namespace App\Contracts;

interface OtpSenderInterface
{
    public function send(string $recipient, string $code, int $expiresMinutes): void;
}
