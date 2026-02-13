<?php

namespace App\Data\Checkpoint;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class ClaimCheckpointData extends Data
{
    public function __construct(
        #[MapInputName('deadline_at')]
        public ?string $deadlineAt = null,
    ) {}
}
