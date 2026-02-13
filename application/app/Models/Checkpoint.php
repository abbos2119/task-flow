<?php

namespace App\Models;

use App\Models\Traits\CheckpointScopeTrait;
use App\Support\WorkflowHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $task_id
 * @property int|null $responsible_id
 * @property Carbon|null $started_at
 * @property Carbon|null $end_at
 * @property Carbon|null $deadline_at
 * @property string|null $status
 * @property string|null $end_transition
 * @property string|null $end_comment
 * @property array|null $transition_names
 * @property array|null $visible_to_permissions
 * @property array|null $visible_to_user_ids
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Task $task
 * @property-read User|null $responsible
 * @property-read array $my_enabled_transitions
 */
class Checkpoint extends Model
{
    use CheckpointScopeTrait;

    public const string STATUS_PENDING = 'pending';
    public const string STATUS_CLAIMED = 'claimed';
    public const string STATUS_IN_PROGRESS = 'in_progress';
    public const string STATUS_DONE = 'done';

    protected $guarded = ['id'];

    protected $appends = ['my_enabled_transitions'];

    protected function casts(): array
    {
        return [
            'transition_names' => 'array',
            'visible_to_permissions' => 'array',
            'visible_to_user_ids' => 'array',
            'started_at' => 'datetime',
            'end_at' => 'datetime',
            'deadline_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }


    public function getMyEnabledTransitionsAttribute(): array
    {
        $user = auth()->user();
        if ($this->status !== self::STATUS_IN_PROGRESS || !$user || $this->responsible_id !== $user->id) return [];

        $task = $this->task;
        if (!$task || empty($this->transition_names)) return [];

        return collect($this->transition_names)
            ->map(fn($name) => (string) $name)
            ->filter(function ($name) use ($task, $user) {
                $permission = WorkflowHelper::getPermissionForTransition($task->getTaskTypeName(), $task->present_state, $name);
                return !$permission || $user->hasRole($permission);
            })
            ->values()
            ->toArray();
    }
}
