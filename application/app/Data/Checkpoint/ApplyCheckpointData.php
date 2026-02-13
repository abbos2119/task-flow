<?php

namespace App\Data\Checkpoint;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class ApplyCheckpointData extends Data
{
    public function __construct(
        #[MapInputName('transition_name')]
        public string $transitionName,
        #[MapInputName('end_comment')]
        public ?string $endComment = null,
        public ?array $context = null,
    ) {}

    public function toContext(): TransitionContext
    {
        return new TransitionContext(
            endComment: $this->endComment,
            extra: $this->context ?? [],
        );
    }
}
