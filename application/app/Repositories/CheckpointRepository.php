<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\Checkpoint\CheckpointFilterData;
use App\Models\Checkpoint;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Throwable;

readonly class CheckpointRepository
{
    private const array ALLOWED_INCLUDES = ['task', 'responsible'];

    /** @throws Throwable */
    public function saveOrFail(Checkpoint $checkpoint): void
    {
        $checkpoint->saveOrFail();
    }

    public function findOrFail(int $id, array $include = []): Checkpoint
    {
        return Checkpoint::query()
            ->when(
                $this->resolveRelations($include),
                fn ($q, $relations) => $q->with($relations),
            )
            ->findOrFail($id);
    }

    public function paginate(CheckpointFilterData $filter): LengthAwarePaginator
    {
        return Checkpoint::query()
            ->when($filter->taskId, fn ($q, $taskId) => $q->forTask($taskId))
            ->when(
                $this->resolveRelations($filter->include),
                fn ($q, $relations) => $q->with($relations),
            )
            ->visibleTo((string) Auth::id(), $this->userPermissions())
            ->latest()
            ->paginate($filter->perPage, ['*'], 'page', $filter->page);
    }

    /** @return string[]|null */
    private function resolveRelations(array $include): ?array
    {
        $relations = array_intersect($include, self::ALLOWED_INCLUDES);
        return $relations !== [] ? $relations : null;
    }

    /** @return string[] */
    private function userPermissions(): array
    {
        return Auth::user()?->getAllPermissions()->pluck('name')->toArray() ?? [];
    }
}
