<?php

namespace App\Http\Controllers;

use App\Data\Checkpoint\ApplyCheckpointData;
use App\Data\Checkpoint\AssignCheckpointData;
use App\Data\Checkpoint\CheckpointData;
use App\Data\Checkpoint\CheckpointShowFilterData;
use App\Data\Checkpoint\ClaimCheckpointData;
use App\Services\CheckpointService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

#[OA\Tag(name: 'Checkpoints', description: 'Checkpoint show and actions')]
readonly class CheckpointController extends Controller
{
    public function __construct(
        private CheckpointService $service,
    ) {}

    #[OA\Get(
        path: '/api/v1/task-management/checkpoints/{id}',
        summary: 'Single checkpoint by ID',
        security: [['sanctum' => []]],
        tags: ['Checkpoints'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'include', description: 'Relations: task, responsible', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(int $id, Request $request): CheckpointData
    {
        $filter = CheckpointShowFilterData::from($request->query());
        $checkpoint = $this->service->find($id, $filter->include);
        return CheckpointData::fromModel($checkpoint);
    }

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/v1/task-management/checkpoints/{id}/apply',
        summary: 'Apply workflow transition to checkpoint',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['transition_name'],
                properties: [
                    new OA\Property(property: 'transition_name', type: 'string', example: 'start_fixing'),
                    new OA\Property(property: 'end_comment', type: 'string', nullable: true),
                    new OA\Property(property: 'context', type: 'object', nullable: true),
                ]
            )
        ),
        tags: ['Checkpoints'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function apply(int $id, ApplyCheckpointData $data): CheckpointData
    {
        $checkpoint = $this->service->apply($id, auth()->id(), $data);
        return CheckpointData::fromModel($checkpoint);
    }

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/v1/task-management/checkpoints/{id}/assign',
        summary: 'Assign responsible user to checkpoint',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['responsible_id'],
                properties: [
                    new OA\Property(property: 'responsible_id', description: 'User ID', type: 'string'),
                    new OA\Property(property: 'deadline_at', type: 'string', example: '2026-12-31 23:59:59', nullable: true),
                ]
            )
        ),
        tags: ['Checkpoints'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function assign(int $id, AssignCheckpointData $data): CheckpointData
    {
        $checkpoint = $this->service->assign($id, $data);
        return CheckpointData::fromModel($checkpoint);
    }

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/v1/task-management/checkpoints/{id}/claim',
        summary: 'Claim checkpoint (assign to current user)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [new OA\Property(property: 'deadline_at', type: 'string', nullable: true)]
            )
        ),
        tags: ['Checkpoints'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function claim(int $id, ClaimCheckpointData $data): CheckpointData
    {
        $checkpoint = $this->service->claim($id, auth()->id(), $data->deadlineAt);
        return CheckpointData::fromModel($checkpoint);
    }

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/v1/task-management/checkpoints/{id}/start',
        summary: 'Start checkpoint',
        security: [['sanctum' => []]],
        tags: ['Checkpoints'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function start(int $id): CheckpointData
    {
        $checkpoint = $this->service->start($id, auth()->id());
        return CheckpointData::fromModel($checkpoint);
    }
}
