<?php

namespace App\Data\Checkpoint;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class AssignCheckpointData extends Data
{
    public function __construct(
        #[MapInputName('responsible_id')]
        public string $responsibleId,
        #[MapInputName('deadline_at')]
        public ?string $deadlineAt = null,
    ) {}
}
