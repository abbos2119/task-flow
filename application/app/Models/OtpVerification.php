<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $identifier
 * @property string $code
 * @property string $type
 * @property int $attempts
 * @property Carbon $expires_at
 * @property Carbon|null $verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OtpVerification extends Model
{
    public const string TYPE_EMAIL = 'email';
    public const string TYPE_PHONE = 'phone';

    protected $fillable = [
        'identifier',
        'code',
        'type',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $hidden = [
        'code',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function scopeRecentForIdentifier(Builder $query, string $identifier, int $minutes): Builder
    {
        return $query
            ->where('identifier', $identifier)
            ->where('created_at', '>=', now()->subMinutes($minutes));
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function markVerified(): void
    {
        $this->verified_at = now();
    }
}
