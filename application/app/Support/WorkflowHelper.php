<?php

declare(strict_types=1);

namespace App\Support;

final class WorkflowHelper
{
    private static function getConfigForTaskType(string $taskTypeName): array
    {
        return config("workflow.mapping.{$taskTypeName}", []);
    }

    public static function getAllTaskTypeConfigs(): array
    {
        return config('workflow.mapping', []);
    }


    private static function getTransitionsDefinedFromState(string $taskTypeName, string $currentStateName): array
    {
        $config = self::getConfigForTaskType($taskTypeName);
        $states = $config['states'] ?? [];
        return $states[$currentStateName]['transitions'] ?? [];
    }

    public static function getRuleClassForTransition(
        string $taskTypeName,
        string $fromStateName,
        string $transitionName,
    ): ?string {
        $transitionsFromState = self::getTransitionsDefinedFromState($taskTypeName, $fromStateName);
        $transitionConfig = $transitionsFromState[$transitionName] ?? null;
        return $transitionConfig['rule_class'] ?? null;
    }

    public static function getRequiredPermissionNamesForState(string $taskTypeName, ?string $currentStateName): array
    {
        if ($currentStateName === null || $currentStateName === '') {
            return [];
        }
        $transitionsFromState = self::getTransitionsDefinedFromState($taskTypeName, $currentStateName);
        $permissionNames = [];
        foreach ($transitionsFromState as $transitionConfig) {
            if (!empty($transitionConfig['permission'])) {
                $permissionNames[] = $transitionConfig['permission'];
            }
        }
        return array_values(array_unique($permissionNames));
    }

    public static function getPermissionForTransition(
        string $taskTypeName,
        string $fromStateName,
        string $transitionName,
    ): ?string {
        $transitionsFromState = self::getTransitionsDefinedFromState($taskTypeName, $fromStateName);
        $transitionConfig = $transitionsFromState[$transitionName] ?? null;
        return $transitionConfig['permission'] ?? null;
    }

}