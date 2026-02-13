<?php

namespace App\Services\Rules;

use App\Contracts\TransitionRule;
use App\Data\Checkpoint\TransitionContext;
use App\Models\Checkpoint;
use App\Models\Task;

final class TakeIssueTransitionRule implements TransitionRule
{
    public static function handle(Task $task, Checkpoint $checkpoint, TransitionContext $context): void {}
}
