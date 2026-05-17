<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiUsageLog extends Model
{
    protected $table = 'ai_usage_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'user_id',
        'action_type',
        'provider',
        'model',
        'prompt_hash',
        'prompt_tokens',
        'completion_tokens',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
