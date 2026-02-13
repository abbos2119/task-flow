<?php

declare(strict_types=1);

namespace App\Services\Otp;

use App\Contracts\OtpSenderInterface;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;

final readonly class EmailOtpSender implements OtpSenderInterface
{
    public function send(string $recipient, string $code, int $expiresMinutes): void
    {
        Mail::to($recipient)->send(new OtpMail($code, $expiresMinutes));
    }
}
