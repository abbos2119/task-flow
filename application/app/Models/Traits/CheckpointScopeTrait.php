<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CheckpointScopeTrait
{
    public function scopeForTask(Builder $query, string $taskId): Builder
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeCanView(Builder $query, string $userId, array $permissions = []): Builder
    {
        return $query->where(function (Builder $q) use ($userId, $permissions) {
            $q->whereJsonContains('visible_to_user_ids', $userId)
              ->orWhere('responsible_id', $userId);
            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    $q->orWhereJsonContains('visible_to_permissions', $permission);
                }
            }
        });
    }

    public function scopeVisibleTo(Builder $query, string $userId, array $permissions = []): Builder
    {
        return $query->canView($userId, $permissions);
    }
}
