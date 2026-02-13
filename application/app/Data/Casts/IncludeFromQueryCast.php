<?php

namespace App\Data\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

/**
 * Casts query "include" (string "a,b,c" or array) to list of trimmed strings.
 */
final class IncludeFromQueryCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }
        if ($value === null || $value === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }
}
