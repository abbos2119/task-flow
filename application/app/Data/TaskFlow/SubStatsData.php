<?php

declare(strict_types=1);

namespace App\Data\TaskFlow;

use Spatie\LaravelData\Data;

class SubStatsData extends Data
{
    public function __construct(
        public int $pending,
        public int $claimed,
        public int $inProgress,
        public int $done,
    ) {}
}
