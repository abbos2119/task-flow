<?php

declare(strict_types=1);

namespace App\Services\Otp;

use App\Contracts\OtpSenderInterface;

final readonly class SmsOtpSender implements OtpSenderInterface
{
    public function send(string $recipient, string $code, int $expiresMinutes): void
    {
        // TODO: sending otp via sms
    }
}
