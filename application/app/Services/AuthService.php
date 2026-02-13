<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\RoleNames;
use App\Data\Auth\AuthTokenData;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Laravel\Sanctum\NewAccessToken;
use Throwable;

final readonly class AuthService
{
    private const string DEFAULT_DEVICE = 'default';

    public function __construct(
        private OtpService $otpService,
        private UserRepository $userRepo,
    ) {}

    /** @throws AuthenticationException|Throwable */
    public function loginWithPasswordAndCreateToken(string $loginOrEmail, string $password, string $deviceName = self::DEFAULT_DEVICE): AuthTokenData
    {
        $user = $this->userRepo->findByLoginOrEmail($loginOrEmail);
        if (!$user?->password || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('Invalid login or password.');
        }
        $token = $this->createToken($user, $deviceName);
        return AuthTokenData::fromUserAndToken($user, $token->plainTextToken);
    }

    /** @throws Throwable */
    public function loginWithOtpAndCreateToken(string $identifier, string $code, string $deviceName = self::DEFAULT_DEVICE): AuthTokenData
    {
        $otp = $this->otpService->verify($identifier, $code);
        if (!$otp) {
            throw new InvalidArgumentException('Invalid or expired OTP code.');
        }
        $user = $this->userRepo->findByIdentifier($otp->type, $identifier);
        if (!$user) {
            throw new InvalidArgumentException('User not found. Please register first.');
        }
        return DB::transaction(function () use ($user, $otp, $deviceName): AuthTokenData {
            $this->otpService->markVerified($otp);
            $token = $this->createToken($user, $deviceName);
            return AuthTokenData::fromUserAndToken($user, $token->plainTextToken);
        });
    }

    /** @throws Throwable */
    public function registerUser(string $email, string $login, string $password, string $deviceName = self::DEFAULT_DEVICE): AuthTokenData
    {
        return DB::transaction(function () use ($email, $login, $password, $deviceName): AuthTokenData {
            $user = new User();
            $user->email = $email;
            $user->login = $login;
            $user->password = $password;
            $this->userRepo->saveOrFail($user);
            $this->ensureEmployeeRole($user);
            $token = $this->createToken($user, $deviceName);
            return AuthTokenData::fromUserAndToken($user, $token->plainTextToken);
        });
    }

    /** @throws Throwable */
    public function createToken(User $user, string $deviceName = self::DEFAULT_DEVICE): NewAccessToken
    {
        return DB::transaction(function () use ($user, $deviceName) {
            $user->tokens()->where('name', $deviceName)->delete();
            return $user->createToken($deviceName);
        });
    }

    private function ensureEmployeeRole(User $user): void
    {
        if (!$user->hasRole(RoleNames::EMPLOYEE)) {
            $user->assignRole(RoleNames::EMPLOYEE);
        }
    }

    public function isLoginAvailable(string $login): bool
    {
        return !$this->userRepo->isLoginTaken($login);
    }
}
