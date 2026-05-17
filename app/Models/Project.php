<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

final class Project extends Model
{
    use Searchable, SoftDeletes;

    protected $table = 'projects';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'organization_period_id',
        'project_template_id',
        'project_lead_id',
        'name',
        'slug',
        'description',
        'status',
        'progress',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'progress' => 'integer',
        ];
    }

    /**
     * @return array<string, string|int|null>
     */
    public function toSearchableArray(): array
    {
        return [
            'organization_id' => (int) $this->organization_id,
            'name' => (string) $this->name,
            'description' => $this->description === null ? null : (string) $this->description,
            'status' => (string) $this->status,
            'period_name' => $this->organization_period_id === null ? null : (string) $this->organizationPeriod?->name,
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function projectLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_lead_id');
    }

    public function organizationPeriod(): BelongsTo
    {
        return $this->belongsTo(OrganizationPeriod::class);
    }
}
