<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Task\TaskCheckpointHistoryFilterData;
use App\Models\Checkpoint;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Throwable;

readonly class TaskRepository
{
    private const array TASK_INCLUDES = ['responsible', 'checkpoints', 'firstCheckpoint', 'lastCheckpoint'];
    private const array CHECKPOINT_INCLUDES = ['task', 'responsible'];

    public function findOrFail(string $id, array $include = []): Task
    {
        return Task::query()
            ->when(
                $this->resolveRelations($include, self::TASK_INCLUDES),
                fn ($q, $relations) => $q->with($relations),
            )
            ->findOrFail($id);
    }

    public function checkpointHistory(string $taskId, TaskCheckpointHistoryFilterData $filter): LengthAwarePaginator
    {
        Task::query()->findOrFail($taskId);
        return Checkpoint::query()
            ->forTask($taskId)
            ->when(
                $this->resolveRelations($filter->include, self::CHECKPOINT_INCLUDES),
                fn ($q, $relations) => $q->with($relations),
            )
            ->latest()
            ->paginate($filter->perPage, ['*'], 'page', $filter->page);
    }

    /** @throws Throwable */
    public function saveOrFail(Task $task): bool
    {
        return $task->saveOrFail();
    }

    /** @return string[]|null */
    private function resolveRelations(array $include, array $allowed): ?array
    {
        $relations = array_intersect($include, $allowed);
        return $relations !== [] ? $relations : null;
    }
}
