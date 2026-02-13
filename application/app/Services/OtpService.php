<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\OtpSenderInterface;
use App\Models\OtpVerification;
use App\Repositories\OtpVerificationRepository;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

final readonly class OtpService
{
    private const int MAX_VERIFY_ATTEMPTS = 5;

    public function __construct(
        private OtpVerificationRepository $repo,
        private OtpSenderInterface $sender,
    ) {}

    /** @throws Throwable */
    public function send(string $identifier, string $type = OtpVerification::TYPE_EMAIL): OtpVerification
    {
        $rateLimitMinutes = (int) config('auth_otp.rate_limit_minutes', 1);
        $expiresMinutes = (int) config('auth_otp.expires_minutes', 5);
        if ($this->repo->hasRecentForIdentifier($identifier, $rateLimitMinutes)) {
            throw new TooManyRequestsHttpException(
                $rateLimitMinutes * 60,
                "Please wait at least {$rateLimitMinutes} minute(s) before requesting a new code.",
            );
        }
        $plainCode = $this->generateCode();
        $otp = new OtpVerification();
        $otp->identifier = $identifier;
        $otp->code = Hash::make($plainCode);
        $otp->type = $type;
        $otp->expires_at = now()->addMinutes($expiresMinutes);
        $this->repo->saveOrFail($otp);
        $this->sender->send($identifier, $plainCode, $expiresMinutes);
        return $otp;
    }

    /** @throws TooManyRequestsHttpException|Throwable */
    public function verify(string $identifier, string $code): ?OtpVerification
    {
        $otp = $this->repo->findLatestUnverifiedForIdentifier($identifier);
        if (!$otp) {
            return null;
        }
        if ($otp->attempts >= self::MAX_VERIFY_ATTEMPTS) {
            throw new TooManyRequestsHttpException(null, 'Too many verification attempts. Please request a new code.');
        }
        $otp->attempts++;
        $this->repo->saveOrFail($otp);
        return !$otp->isExpired() && Hash::check($code, $otp->code) ? $otp : null;
    }

    /** @throws Throwable */
    public function markVerified(OtpVerification $otp): void
    {
        $otp->markVerified();
        $this->repo->saveOrFail($otp);
    }

    private function generateCode(): string
    {
        $length = (int) config('auth_otp.code_length', 6);
        return (string) random_int(10 ** ($length - 1), 10 ** $length - 1);
    }
}
