<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Project extends Model
{
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function projectLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_lead_id');
    }
}
