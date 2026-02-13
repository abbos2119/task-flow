<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\TransitionRule;
use App\Events\WorkflowTransitionEnded;
use App\Support\WorkflowHelper;

final readonly class WorkflowTransitionEndedListener
{
    public function handle(WorkflowTransitionEnded $event): void
    {
        $fromState = $event->context->fromState;
        $ruleClass = WorkflowHelper::getRuleClassForTransition($event->task->task_type, $fromState, $event->transitionName);
        if (!$ruleClass || !is_subclass_of($ruleClass, TransitionRule::class)) {
            return;
        }
        $ruleClass::handle($event->task, $event->checkpoint, $event->context);
    }
}
