<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Organization\Enums\PlanTier;
use Illuminate\Database\Eloquent\Model;

final class Organization extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'status',
        'plan_tier',
        'internal_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan_tier' => PlanTier::class,
        ];
    }
}
