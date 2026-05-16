<?php

declare(strict_types=1);

namespace App\Actions\SuperAdmin;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

final class LogActivityAction
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(
        string $action,
        Model $target,
        array $payload = [],
        ?int $actorUserId = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $actorUserId ?? auth()->id(),
            'action' => $action,
            'target_type' => $target::class,
            'target_id' => (int) $target->getKey(),
            'payload' => $payload === [] ? null : $payload,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255) ?: null,
        ]);
    }
}
