<?php

namespace Database\Seeders;

use App\Constants\RoleNames;
use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $login = config('seeders.default_manager.login');
        $user = User::query()->firstOrCreate(
            ['login' => $login],
            [
                'email' => config('seeders.default_manager.email'),
                'password' => config('seeders.default_manager.password'),
            ]
        );

        if (!$user->hasRole(RoleNames::MANAGER)) {
            $user->assignRole(RoleNames::MANAGER);
        }
    }
}
