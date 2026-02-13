<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\TaskFlow\TaskFlowScope;
use App\Models\{Checkpoint, Task};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Support\Facades\DB;

final readonly class TaskFlowRepository
{

    public function getStateCounts(TaskFlowScope $scope): array
    {
        return $this->scopedQuery($scope)
            ->groupBy('present_state')
            ->select('present_state', DB::raw('count(*) as total'))
            ->pluck('total', 'present_state')
            ->all();
    }


    public function getSubStatusCounts(TaskFlowScope $scope, array $states): array
    {
        $latestCheckpointIds = Checkpoint::query()
            ->selectRaw('MAX(id)')
            ->whereIn('task_id', $this->scopedQuery($scope)->whereIn('present_state', $states)->select('id'))
            ->groupBy('task_id');

        return Checkpoint::query()
            ->whereIn('id', $latestCheckpointIds)
            ->groupBy('status')
            ->select('status', DB::raw('count(*) as total'))
            ->pluck('total', 'status')
            ->all();
    }

    public function getTasksPaginated(
        TaskFlowScope $scope,
        ?array $states,
        ?string $checkpointStatus,
        int $perPage,
        int $page
    ): LengthAwarePaginator {
        return $this->scopedQuery($scope)
            ->with(['lastCheckpoint.responsible'])
            ->when($states, fn(Builder $q) => $q->whereIn('present_state', $states))
            ->when($checkpointStatus, function (Builder $q) use ($checkpointStatus) {
                $q->whereHas('lastCheckpoint', fn($bq) => $bq->where('status', $checkpointStatus));
            })
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }


    private function scopedQuery(TaskFlowScope $scope): Builder
    {
        return Task::query()
            ->where('status', Task::STATUS_ACTIVE)
            ->when(!$scope->isManager, function (Builder $q) use ($scope) {
                $q->whereHas('lastCheckpoint', fn($bq) => $bq->canView($scope->userId, $scope->permissions));
            })
            ->when($scope->mine, function (Builder $q) use ($scope) {
                $q->whereHas('lastCheckpoint', fn($bq) => $bq->where('responsible_id', $scope->userId));
            });
    }
}