<?php

declare(strict_types=1);

namespace App\Data\TaskFlow;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class TaskFlowFilterData extends Data
{
    public function __construct(
        #[MapInputName('stage_id')]
        public ?string $stageId = null,
        #[MapInputName('checkpoint_status')]
        public ?string $checkpointStatus = null,
        public bool $mine = false,
        #[MapInputName('per_page')]
        public int $perPage = 15,
        public int $page = 1,
    ) {}
}
