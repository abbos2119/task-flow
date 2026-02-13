<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

trait TaskWorkflowTrait
{
    public function getPresentState(): ?string
    {
        return $this->present_state;
    }

    public function setPresentState(string $state, array $context = []): void
    {
        $this->present_state = $state;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getEnabledTransitions(): array
    {
        $workflow = $this->getWorkflow();
        return $workflow ? $workflow->getEnabledTransitions($this) : [];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getEnabledTransitionsNames(): array
    {
        $names = [];
        foreach ($this->getEnabledTransitions() as $transition) {
            $names[] = $transition->getName();
        }
        return $names;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    public function applyTransition(string $transitionName, array $context = []): void
    {
        $workflow = $this->getWorkflow();
        if (!$workflow) {
            throw new RuntimeException("Workflow not found for task type: {$this->getTaskTypeName()}");
        }
        $workflow->apply($this, $transitionName, $context);
        $this->saveOrFail();
    }
}
