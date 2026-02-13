<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Task;
use App\Support\WorkflowHelper;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

final class WorkflowSetupService
{
    private MethodMarkingStore $markingStore;

    public function __construct(
        private Registry $registry,
    ) {
        $this->markingStore = new MethodMarkingStore(true, 'presentState');
    }

    public function setup(): void
    {
        $configs = WorkflowHelper::getAllTaskTypeConfigs();
        foreach ($configs as $taskTypeName => $config) {
            $definition = $this->parseDefinition($taskTypeName, $config);
            $workflow = new Workflow($definition, $this->markingStore, null, $taskTypeName);
            $this->registry->addWorkflow($workflow, new InstanceOfSupportStrategy(Task::class));
        }
    }

    private function parseDefinition(string $name, array $config): Definition
    {
        $states = $config['states'] ?? [];
        $places = array_keys($states);
        $transitions = [];
        foreach ($states as $from => $stateConfig) {
            foreach ($stateConfig['transitions'] ?? [] as $tName => $t) {
                $transitions[] = new Transition($tName, $from, $t['to'] ?? $from);
            }
        }
        return new Definition($places, $transitions, $config['initial_state'] ?? $places[0]);
    }
}