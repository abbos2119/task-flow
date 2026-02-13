<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Checkpoint\ApplyCheckpointData;
use App\Data\Checkpoint\AssignCheckpointData;
use App\Data\Checkpoint\CheckpointFilterData;
use App\Data\Checkpoint\TransitionContext;
use App\Events\WorkflowTransitionEnded;
use App\Models\Checkpoint;
use App\Models\Task;
use App\Repositories\CheckpointRepository;
use App\Support\WorkflowHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final readonly class CheckpointService
{
    public function __construct(
        private CheckpointRepository $repository,
    ) {}

    public function list(CheckpointFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->paginate($filter);
    }

    public function find(int $id, array $include = []): Checkpoint
    {
        return $this->repository->findOrFail($id, $include);
    }

    /** @throws Throwable */
    public function createForTask(Task $task, int $userId, ?WorkflowTransitionEnded $event = null): Checkpoint
    {
        $checkpoint = new Checkpoint();
        $checkpoint->task_id = $task->id;
        $checkpoint->transition_names = $task->getEnabledTransitionsNames();
        $checkpoint->deadline_at = $event?->context->extra['deadline_at'] ?? null;
        $checkpoint->status = Checkpoint::STATUS_PENDING;
        $checkpoint->created_by = $userId;
        $checkpoint->setRelation('task', $task);
        $this->refreshVisibility($checkpoint, $event?->checkpoint->visible_to_user_ids ?? []);
        $this->repository->saveOrFail($checkpoint);
        return $checkpoint;
    }

    /** @throws Throwable */
    public function apply(int $checkpointId, int $userId, ApplyCheckpointData $data): Checkpoint
    {
        $checkpoint = $this->repository->findOrFail($checkpointId);
        return DB::transaction(function () use ($checkpoint, $userId, $data) {
            $checkpoint->loadMissing('task');
            $task = $checkpoint->task;
            $this->ensureNotDone($checkpoint);
            $this->ensureResponsible($checkpoint, $userId);
            $context = $data->toContext();
            $context->checkpoint = $checkpoint;
            $context->fromState = $task->present_state;
            $task->applyTransition($data->transitionName, $context->toArray());
            $this->end($checkpoint, $data->transitionName, $context);
            event(new WorkflowTransitionEnded($task, $checkpoint, $data->transitionName, $context));
            return $checkpoint->fresh();
        });
    }

    /** @throws Throwable */
    public function assign(int $checkpointId, AssignCheckpointData $data): Checkpoint
    {
        $checkpoint = $this->repository->findOrFail($checkpointId);
        return DB::transaction(function () use ($checkpoint, $data) {
            $checkpoint->responsible_id = $data->responsibleId;
            $checkpoint->deadline_at = $data->deadlineAt;
            $checkpoint->status = Checkpoint::STATUS_CLAIMED;
            $this->refreshVisibility($checkpoint, $checkpoint->visible_to_user_ids ?? []);
            $this->repository->saveOrFail($checkpoint);
            return $checkpoint->fresh();
        });
    }

    /** @throws Throwable */
    public function claim(int $checkpointId, int $userId, ?string $deadlineAt = null): Checkpoint
    {
        $checkpoint = $this->repository->findOrFail($checkpointId);
        return DB::transaction(function () use ($checkpoint, $userId, $deadlineAt) {
            if ($checkpoint->status !== Checkpoint::STATUS_PENDING) {
                throw new RuntimeException("Checkpoint is not claimable: {$checkpoint->id}");
            }
            if ($checkpoint->responsible_id) {
                throw new RuntimeException('You can not claim this task');
            }
            if (!$this->isVisibleTo($checkpoint, (string)$userId)) {
                throw new RuntimeException('You can not claim this task');
            }
            $checkpoint->responsible_id = $userId;
            $checkpoint->deadline_at = $deadlineAt;
            $checkpoint->status = Checkpoint::STATUS_CLAIMED;
            $this->refreshVisibility($checkpoint, $checkpoint->visible_to_user_ids ?? []);
            $this->repository->saveOrFail($checkpoint);
            return $checkpoint->fresh();
        });
    }

    /** @throws Throwable */
    public function start(int $checkpointId, int $userId): Checkpoint
    {
        $checkpoint = $this->repository->findOrFail($checkpointId);
        return DB::transaction(function () use ($checkpoint, $userId) {
            if ($checkpoint->status !== Checkpoint::STATUS_CLAIMED) {
                throw new RuntimeException("Checkpoint should be claimed first: {$checkpoint->id}");
            }
            $this->ensureResponsible($checkpoint, $userId);
            $checkpoint->started_at = now();
            $checkpoint->status = Checkpoint::STATUS_IN_PROGRESS;
            $this->repository->saveOrFail($checkpoint);
            return $checkpoint->fresh();
        });
    }

    public function refreshVisibility(Checkpoint $checkpoint, ?array $parentVisibleToUserIds = null): void
    {
        $task = $checkpoint->task;
        $permissions = WorkflowHelper::getRequiredPermissionNamesForState($task->getTaskTypeName(), $task->present_state);
        $checkpoint->visible_to_permissions = array_values(array_filter(array_unique($permissions)));
        $userIds = array_filter([
            $task?->created_by,
            $checkpoint->created_by,
            $checkpoint->responsible_id,
        ]);
        $checkpoint->visible_to_user_ids = array_values(array_unique(
            array_merge($parentVisibleToUserIds ?? [], $userIds),
        ));
    }

    public function isVisibleTo(Checkpoint $checkpoint, string $userId, array $permissions = []): bool
    {
        return in_array($userId, $checkpoint->visible_to_user_ids ?? [], true)
            || !empty(array_intersect($permissions, $checkpoint->visible_to_permissions ?? []));
    }

    private function ensureNotDone(Checkpoint $checkpoint): void
    {
        if ($checkpoint->status === Checkpoint::STATUS_DONE) {
            throw new RuntimeException("Checkpoint already ended: {$checkpoint->id}");
        }
    }

    private function ensureResponsible(Checkpoint $checkpoint, int $userId): void
    {
        if ($checkpoint->responsible_id !== $userId) {
            throw new RuntimeException("You are not responsible for this checkpoint: {$checkpoint->id}");
        }
    }

    /**
     * @throws Throwable
     */
    private function end(Checkpoint $checkpoint, string $transition, TransitionContext $context): void
    {
        $checkpoint->end_at = now();
        $checkpoint->end_comment = $context->endComment;
        $checkpoint->end_transition = $transition;
        $checkpoint->status = Checkpoint::STATUS_DONE;
        $this->repository->saveOrFail($checkpoint);
    }
}
