<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\WorkflowTransitionEnded;
use App\Services\CheckpointService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

final readonly class AddCheckpointAfterTransitionListener
{
    public function __construct(
        private CheckpointService $checkpointService,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    public function handle(WorkflowTransitionEnded $event): void
    {
        $task = $event->task;
        if (empty($task->getEnabledTransitionsNames())) {
            return;
        }
        $this->checkpointService->createForTask($task, (int)auth()->id(), $event);
    }
}
