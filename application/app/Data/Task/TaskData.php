<?php

namespace App\Data\Task;

use App\Data\Checkpoint\CheckpointData;
use App\Models\Task;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

class TaskData extends Data
{

    public function __construct(
        public string $id,
        public string $taskType,
        public ?string $title,
        public ?string $description,
        public ?string $presentState,
        public ?string $responsibleId,
        public ?string $createdBy,
        public ?string $updatedBy,
        public ?string $createdAt,
        public ?string $updatedAt,
        public Lazy|DataCollection $checkpoints,
        public Lazy|CheckpointData|null $firstCheckpoint = null,
        public Lazy|CheckpointData|null $lastCheckpoint = null,
    ) {}

    public static function fromModel(Task $task): static
    {
        return new static(
            id: $task->id,
            taskType: $task->task_type,
            title: $task->title,
            description: $task->description,
            presentState: $task->present_state,
            responsibleId: $task->responsible_id,
            createdBy: $task->created_by,
            updatedBy: $task->updated_by,
            createdAt: $task->created_at?->toIso8601String(),
            updatedAt: $task->updated_at?->toIso8601String(),
            checkpoints: Lazy::whenLoaded('checkpoints', $task, fn () => CheckpointData::collect($task->checkpoints, DataCollection::class)),
            firstCheckpoint: Lazy::whenLoaded('firstCheckpoint', $task, fn () => $task->firstCheckpoint ? CheckpointData::fromModel($task->firstCheckpoint) : null),
            lastCheckpoint: Lazy::whenLoaded('lastCheckpoint', $task, fn () => $task->lastCheckpoint ? CheckpointData::fromModel($task->lastCheckpoint) : null),
        );
    }
}
