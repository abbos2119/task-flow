<?php

declare(strict_types=1);

namespace App\Data\Checkpoint;

use App\Models\Checkpoint;

class TransitionContext
{
    public function __construct(
        public ?Checkpoint $checkpoint = null,
        public ?string $endComment = null,
        public ?string $fromState = null,
        public array $extra = [],
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'checkpoint' => $this->checkpoint,
            'end_comment' => $this->endComment,
            'from_state' => $this->fromState,
            ...$this->extra,
        ], fn ($v) => $v !== null);
    }
}
