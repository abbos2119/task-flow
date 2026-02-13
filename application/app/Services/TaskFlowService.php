<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\TaskFlow\{StageStatData, TaskFlowFilterData, TaskFlowScope};
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

        $result = [];
        foreach (config('task_flow.pipeline.stages', []) as $config) {
            $total = 0;
            foreach ($config['task_states'] ?? [] as $state) {
                $total += $counts[$state] ?? 0;
            }
            $result[] = new StageStatData(
                id:         $config['identifier'],
                label:      $config['display_name'],
                totalTasks: $total,
            );
        }

        return $result;
    }


    public function getSubStats(User $user, string $stageId, bool $mine): ?array
    {
        $states = $this->getStatesByStageId($stageId);
        if (empty($states)) return null;

        $counts = $this->repository->getSubStatusCounts(TaskFlowScope::fromUser($user, $mine), $states);

        $subStatuses = [
            Checkpoint::STATUS_PENDING     => 'Pending',
            Checkpoint::STATUS_CLAIMED     => 'Claimed',
            Checkpoint::STATUS_IN_PROGRESS => 'In Progress',
            Checkpoint::STATUS_DONE        => 'Done',
        ];

        $result = [];
        foreach ($subStatuses as $id => $label) {
            $result[] = new StageStatData(
                id:         $id,
                label:      $label,
                totalTasks: $counts[$id] ?? 0,
            );
        }

        return $result;
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