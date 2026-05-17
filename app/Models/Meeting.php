<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

final class Meeting extends Model
{
    use Searchable;

    protected $table = 'meetings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'project_id',
        'created_by_user_id',
        'title',
        'agenda',
        'location',
        'starts_at',
        'ends_at',
        'status',
    ];

    /**
     * @return array<string, string|int|null>
     */
    public function toSearchableArray(): array
    {
        return [
            'organization_id' => (int) $this->organization_id,
            'project_id' => $this->project_id === null ? null : (int) $this->project_id,
            'title' => (string) $this->title,
            'agenda' => (string) $this->agenda,
            'location' => $this->location === null ? null : (string) $this->location,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
