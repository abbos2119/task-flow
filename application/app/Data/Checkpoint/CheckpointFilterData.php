<?php

namespace App\Data\Checkpoint;

use App\Data\Casts\IncludeFromQueryCast;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class CheckpointFilterData extends Data
{
    public function __construct(
        #[MapInputName('filter.task_id')]
        public ?string $taskId = null,
        #[WithCast(IncludeFromQueryCast::class)]
        public array $include = [],
        #[MapInputName('per_page')]
        public int $perPage = 15,
        public int $page = 1,
    ) {}
}
