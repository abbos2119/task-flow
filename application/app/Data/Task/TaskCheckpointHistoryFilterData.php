<?php

namespace App\Data\Task;

use App\Data\Casts\IncludeFromQueryCast;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class TaskCheckpointHistoryFilterData extends Data
{
    public function __construct(
        #[WithCast(IncludeFromQueryCast::class)]
        public array $include = [],
        #[MapInputName('per_page')]
        public int $perPage = 15,
        public int $page = 1,
    ) {}
}
