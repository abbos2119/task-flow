<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

readonly class UserRepository
{
    /** @throws Throwable */
    public function saveOrFail(User $user): void
    {
        $user->saveOrFail();
    }

    public function findByEmail(?string $email): ?User
    {
        if (!$email) {
            return null;
        }
        return User::query()->where('email', $email)->first();
    }

    public function findByLogin(?string $login): ?User
    {
        if (!$login) {
            return null;
        }
        return User::query()->where('login', $login)->first();
    }

    public function findByPhone(?string $phone): ?User
    {
        if (!$phone) {
            return null;
        }
        return User::query()->where('phone', $phone)->first();
    }

    public function findByLoginOrEmail(?string $loginOrEmail): ?User
    {
        if (!$loginOrEmail) {
            return null;
        }
        if (str_contains($loginOrEmail, '@')) {
            return $this->findByEmail($loginOrEmail);
        }
        return $this->findByLogin($loginOrEmail);
    }

    public function findByIdentifier(string $type, string $identifier): ?User
    {
        return match ($type) {
            'email' => $this->findByEmail($identifier),
            'phone' => $this->findByPhone($identifier),
            default => null,
        };
    }

    public function isLoginTaken(string $login): bool
    {
        return $this->findByLogin($login) !== null;
    }
}
