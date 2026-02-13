<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\Checkpoint\TransitionContext;
use App\Models\Checkpoint;
use App\Models\Task;

interface TransitionRule
{
    public static function handle(Task $task, Checkpoint $checkpoint, TransitionContext $context): void;
}
