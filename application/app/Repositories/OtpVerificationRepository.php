<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\OtpVerification;
use Throwable;

readonly class OtpVerificationRepository
{
    /** @throws Throwable */
    public function saveOrFail(OtpVerification $otp): void
    {
        $otp->saveOrFail();
    }


    public function findLatestUnverifiedForIdentifier(string $identifier): ?OtpVerification
    {
        return OtpVerification::query()
            ->where('identifier', $identifier)
            ->whereNull('verified_at')
            ->orderByDesc('created_at')
            ->first();
    }

    public function hasRecentForIdentifier(string $identifier, int $minutes): bool
    {
        return OtpVerification::query()
            ->recentForIdentifier($identifier, $minutes)
            ->exists();
    }
}
