<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Constants\RoleNames;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class RoleSeeder extends Seeder
{
    /** @var list<string> */
    private const array ROLES = [
        RoleNames::EMPLOYEE,
        RoleNames::MANAGER,
    ];

    public function run(): void
    {
        $guardName = config('auth.defaults.guard', 'web');
        foreach (self::ROLES as $roleName) {
            Role::query()->firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guardName],
            );
        }
    }
}
