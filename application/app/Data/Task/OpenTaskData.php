<?php

namespace App\Data\Task;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class OpenTaskData extends Data
{
    public function __construct(
        #[MapInputName('task_type')]
        public string $taskType,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $comment = null,
        #[MapInputName('deadline_at')]
        public ?string $deadlineAt = null,
    ) {}
}
