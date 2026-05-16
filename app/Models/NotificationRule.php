<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NotificationRule extends Model
{
    protected $table = 'notification_rules';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'event',
        'label',
        'audience',
        'channels',
        'trigger',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channels' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
