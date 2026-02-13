<?php

namespace App\Data\Checkpoint;

use App\Data\Casts\IncludeFromQueryCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class CheckpointShowFilterData extends Data
{
    public function __construct(
        #[WithCast(IncludeFromQueryCast::class)]
        public array $include = [],
    ) {}
}
