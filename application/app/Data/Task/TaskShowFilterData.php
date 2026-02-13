<?php

namespace App\Data\Task;

use App\Data\Casts\IncludeFromQueryCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class TaskShowFilterData extends Data
{
    public function __construct(
        #[WithCast(IncludeFromQueryCast::class)]
        public array $include = [],
    ) {}
}
