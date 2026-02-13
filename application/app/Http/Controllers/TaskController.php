<?php

namespace App\Http\Controllers;

use App\Data\Checkpoint\CheckpointData;
use App\Data\Task\OpenTaskData;
use App\Data\Task\TaskCheckpointHistoryFilterData;
use App\Data\Task\TaskData;
use App\Data\Task\TaskShowFilterData;
use App\Services\TaskService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\PaginatedDataCollection;
use Throwable;

#[OA\Tag(name: 'Tasks', description: 'Task create, show and history')]
readonly class TaskController extends Controller
{
    public function __construct(
        private TaskService $service,
    ) {}

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/v1/task-management/tasks/open',
        summary: 'Create a new task',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['task_type'],
                properties: [
                    new OA\Property(property: 'task_type', type: 'string', example: 'issue'),
                    new OA\Property(property: 'title', type: 'string', nullable: true),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'comment', type: 'string', nullable: true),
                    new OA\Property(property: 'deadline_at', type: 'string', nullable: true),
                ]
            )
        ),
        tags: ['Tasks'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function open(OpenTaskData $data): TaskData
    {
        $task = $this->service->create($data, auth()->id());
        return TaskData::fromModel($task);
    }

    #[OA\Get(
        path: '/api/v1/task-management/tasks/{id}',
        summary: 'Single task by ID',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Task ID', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'include', in: 'query', required: false, description: 'Relations: checkpoints, firstCheckpoint, lastCheckpoint, responsible', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(string $id, Request $request): TaskData
    {
        $filter = TaskShowFilterData::from($request->query());
        $task = $this->service->find($id, $filter->include);
        return TaskData::fromModel($task);
    }

    #[OA\Get(
        path: '/api/v1/task-management/tasks/{id}/history',
        summary: 'Task checkpoint history (paginated)',
        security: [['sanctum' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Task ID', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'include', in: 'query', required: false, description: 'Relations per checkpoint: task, responsible', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Items per page', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Page number', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function history(string $id, Request $request): PaginatedDataCollection
    {
        $filter = TaskCheckpointHistoryFilterData::from($request->query());
        $paginator = $this->service->checkpointHistory($id, $filter);
        return CheckpointData::collect($paginator, PaginatedDataCollection::class);
    }
}
