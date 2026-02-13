<?php

declare(strict_types=1);

namespace App\Data\TaskFlow;

use Spatie\LaravelData\Data;

class StageStatData extends Data
{
    public function __construct(
        public string $id,
        public string $label,
        public int $totalTasks,
    ) {}
}
