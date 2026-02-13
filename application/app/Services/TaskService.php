<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Task\OpenTaskData;
use App\Data\Task\TaskCheckpointHistoryFilterData;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class TaskService
{
    public function __construct(
        private TaskRepository $taskRepo,
        private CheckpointService $checkpointService,
    ) {}

    public function find(string $id, array $include = []): Task
    {
        return $this->taskRepo->findOrFail($id, $include);
    }

    public function checkpointHistory(string $taskId, TaskCheckpointHistoryFilterData $filter): LengthAwarePaginator
    {
        return $this->taskRepo->checkpointHistory($taskId, $filter);
    }

    /** @throws Throwable */
    public function create(OpenTaskData $data, int $userId): Task
    {
        return DB::transaction(function () use ($data, $userId) {
            $task = new Task();
            $task->task_type = $data->taskType;
            $task->title = $data->title;
            $task->description = $data->description;
            $task->status = Task::STATUS_ACTIVE;
            $task->created_by = $userId;
            $this->taskRepo->saveOrFail($task);
            $this->checkpointService->createForTask($task, $userId);
            $this->taskRepo->saveOrFail($task);
            return $task->fresh();
        });
    }
}
