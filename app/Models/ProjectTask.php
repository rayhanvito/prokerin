<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

final class ProjectTask extends Model
{
    use Searchable;

    protected $table = 'project_tasks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'pic_user_id',
        'title',
        'division',
        'status',
        'due_at',
        'completed_at',
    ];

    /**
     * @return array<string, string|int|null>
     */
    public function toSearchableArray(): array
    {
        return [
            'organization_id' => $this->project?->organization_id === null ? null : (int) $this->project->organization_id,
            'project_id' => (int) $this->project_id,
            'title' => (string) $this->title,
            'division' => $this->division === null ? null : (string) $this->division,
            'status' => (string) $this->status,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }
}
