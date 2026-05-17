<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EventRegistration extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'ticket_tier_id',
        'participant_name',
        'participant_email',
        'phone',
        'institution',
        'status',
        'registered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
