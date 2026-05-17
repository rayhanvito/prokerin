<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Enums\PlanTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Organization extends Model
{
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
        'logo_path',
        'status',
        'plan_tier',
        'internal_notes',
        'onboarding_completed_at',
        'onboarding_step',
        'onboarding_skipped',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan_tier' => PlanTier::class,
            'onboarding_completed_at' => 'datetime',
            'onboarding_skipped' => 'boolean',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
