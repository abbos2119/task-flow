<?php

namespace App\Models;

use App\Models\Traits\TaskScopeTrait;
use App\Models\Traits\TaskWorkflowTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Workflow;

/**
 * @property int $id
 * @property string $task_type
 * @property string|null $title
 * @property string|null $description
 * @property string $present_state
 * @property int|null $responsible_id
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Checkpoint> $checkpoints
 * @property-read Checkpoint|null $firstCheckpoint
 * @property-read Checkpoint|null $lastCheckpoint
 * @property-read User|null $responsible
 */
class Task extends Model
{
    use TaskScopeTrait, TaskWorkflowTrait;

    public const int STATUS_ACTIVE = 1;
    public const int STATUS_INACTIVE = 0;
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'integer'];
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(Checkpoint::class);
    }

    public function firstCheckpoint(): HasOne
    {
        return $this->hasOne(Checkpoint::class)->ofMany('created_at', 'min');
    }

    public function lastCheckpoint(): HasOne
    {
        return $this->hasOne(Checkpoint::class)->ofMany('created_at', 'max');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function getTaskTypeName(): string
    {
        return $this->task_type;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getWorkflow(): ?Workflow
    {
        $registry = app(Registry::class);
        $name = $this->getTaskTypeName();
        return $registry->has($this, $name) ? $registry->get($this, $name) : null;
    }
}
