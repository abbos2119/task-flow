<?php

namespace App\Data\Checkpoint;

use App\Data\Task\TaskData;
use App\Models\Checkpoint;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class CheckpointData extends Data
{
    public function __construct(
        public int $id,
        public int $taskId,
        public ?int $responsibleId,
        public ?string $status,
        public ?string $startedAt,
        public ?string $endAt,
        public ?string $deadlineAt,
        public ?string $endTransition,
        public ?string $endComment,
        public ?array $transitionNames,
        public ?array $visibleToPermissions,
        public ?array $visibleToUserIds,
        public array $myEnabledTransitions,
        public bool $isStarted,
        public bool $isEnded,
        public ?string $createdAt,
        public ?string $updatedAt,
        public Lazy|TaskData|null $task = null,
    ) {}

    public static function fromModel(Checkpoint $c): static
    {
        return new static(
            id: $c->id,
            taskId: $c->task_id,
            responsibleId: $c->responsible_id,
            status: $c->status,
            startedAt: $c->started_at?->toIso8601String(),
            endAt: $c->end_at?->toIso8601String(),
            deadlineAt: $c->deadline_at?->toIso8601String(),
            endTransition: $c->end_transition,
            endComment: $c->end_comment,
            transitionNames: $c->transition_names,
            visibleToPermissions: $c->visible_to_permissions,
            visibleToUserIds: $c->visible_to_user_ids,
            myEnabledTransitions: $c->my_enabled_transitions,
            isStarted: $c->status === Checkpoint::STATUS_IN_PROGRESS || $c->status === Checkpoint::STATUS_DONE,
            isEnded: $c->status === Checkpoint::STATUS_DONE,
            createdAt: $c->created_at?->toIso8601String(),
            updatedAt: $c->updated_at?->toIso8601String(),
            task: Lazy::whenLoaded('task', $c, fn () => TaskData::fromModel($c->task)),
        );
    }
}
