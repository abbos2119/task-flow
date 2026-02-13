<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\TaskFlow\{StageStatData, SubStatsData, TaskFlowFilterData, TaskFlowScope};
use App\Data\Task\TaskData;
use App\Models\{Checkpoint, User};
use App\Repositories\TaskFlowRepository;
use Spatie\LaravelData\PaginatedDataCollection;

final readonly class TaskFlowService
{
    public function __construct(
        private TaskFlowRepository $repository,
    ) {}


    public function getStagesStats(User $user, bool $mine): array
    {
        $scope = TaskFlowScope::fromUser($user, $mine);
        $counts = $this->repository->getStateCounts($scope);
        return array_map(function (array $config) use ($counts) {
            $states = $config['task_states'] ?? [];
            return new StageStatData(
                id:    $config['identifier'],
                label: $config['display_name'],
                color: $config['theme_color'],
                totalTasks: (int) array_sum(array_intersect_key($counts, array_flip($states)))
            );
        }, config('task_flow.pipeline.stages', []));
    }


    public function getSubStats(User $user, string $stageId, bool $mine): ?SubStatsData
    {
        $states = $this->getStatesByStageId($stageId);
        if (empty($states)) return null;
        $counts = $this->repository->getSubStatusCounts(TaskFlowScope::fromUser($user, $mine), $states);
        return new SubStatsData(
            pending:    $counts[Checkpoint::STATUS_PENDING] ?? 0,
            claimed:    $counts[Checkpoint::STATUS_CLAIMED] ?? 0,
            inProgress: $counts[Checkpoint::STATUS_IN_PROGRESS] ?? 0,
            done:       $counts[Checkpoint::STATUS_DONE] ?? 0,
        );
    }


    public function getTaskCollection(User $user, TaskFlowFilterData $filter): PaginatedDataCollection
    {
        $states = $filter->stageId ? $this->getStatesByStageId($filter->stageId) : null;
        $paginator = $this->repository->getTasksPaginated(
            scope:            TaskFlowScope::fromUser($user, $filter->mine),
            states:           $states,
            checkpointStatus: $filter->checkpointStatus,
            perPage:          $filter->perPage,
            page:             $filter->page
        );
        return TaskData::collect($paginator, PaginatedDataCollection::class);
    }


    private function getStatesByStageId(string $stageId): array
    {
        $stage = collect(config('task_flow.pipeline.stages', []))
            ->firstWhere('identifier', $stageId);
        return $stage['task_states'] ?? [];
    }
}