<?php

namespace App\Events;

use App\Data\Checkpoint\TransitionContext;
use App\Models\Checkpoint;
use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowTransitionEnded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Task $task,
        public Checkpoint $checkpoint,
        public string $transitionName,
        public TransitionContext $context = new TransitionContext(),
    ) {}
}
