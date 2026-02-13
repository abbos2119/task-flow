<?php

namespace App\Http\Controllers;

use App\Data\TaskFlow\StageStatData;
use App\Data\TaskFlow\SubStatsData;
use App\Data\TaskFlow\TaskFlowFilterData;
use App\Models\User;
use App\Services\TaskFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

#[OA\Tag(name: 'Task Flow', description: 'Pipeline stages stats and task collection')]
readonly class TaskFlowController extends Controller
{
    public function __construct(
        private TaskFlowService $service,
    ) {}

    #[OA\Get(
        path: '/api/v1/task-flow/stages-stats',
        summary: 'Stage statistics (counts per pipeline stage)',
        security: [['sanctum' => []]],
        tags: ['Task Flow'],
        parameters: [
            new OA\Parameter(name: 'mine', description: 'Only tasks where current user is responsible', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function getStagesStats(Request $request): DataCollection
    {
        /** @var User $user */
        $user = $request->user();
        $mine = filter_var($request->query('mine'), FILTER_VALIDATE_BOOLEAN);
        return StageStatData::collect($this->service->getStagesStats($user, $mine), DataCollection::class);
    }

    #[OA\Get(
        path: '/api/v1/task-flow/sub-stats',
        summary: 'Checkpoint status counts for a stage',
        security: [['sanctum' => []]],
        tags: ['Task Flow'],
        parameters: [
            new OA\Parameter(name: 'stage_id', description: 'Stage identifier', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'mine', description: 'Only tasks where current user is responsible', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Stage not found'),
        ]
    )]
    public function getSubStats(Request $request): SubStatsData|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $this->service->getSubStats($user, (string) $request->query('stage_id'), filter_var($request->query('mine'), FILTER_VALIDATE_BOOLEAN));
        return $data ?? response()->json(['error' => 'Stage not found'], 404);
    }

    #[OA\Get(
        path: '/api/v1/task-flow/collection',
        summary: 'Filtered task list (paginated)',
        security: [['sanctum' => []]],
        tags: ['Task Flow'],
        parameters: [
            new OA\Parameter(name: 'stage_id', description: 'Filter by stage', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'checkpoint_status', description: 'Filter by checkpoint status: pending, claimed, in_progress, done', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'mine', description: 'Only user responsible tasks', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', description: 'Items per page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', description: 'Page number', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function getTaskCollection(Request $request): PaginatedDataCollection
    {
        /** @var User $user */
        $user = $request->user();
        return $this->service->getTaskCollection($user, TaskFlowFilterData::from($request->query()));
    }
}
