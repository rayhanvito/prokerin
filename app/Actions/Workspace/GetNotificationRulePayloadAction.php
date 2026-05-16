<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetNotificationRulePayloadAction
{
    /**
     * @return array<int, array{event: string, label: string, audience: string, channels: array<int, string>, trigger: string, status: string}>
     */
    public function execute(): array
    {
        return DB::table('notification_rules')
            ->orderBy('id')
            ->get()
            ->map(static fn (object $rule): array => [
                'event' => (string) $rule->event,
                'label' => (string) $rule->label,
                'audience' => (string) $rule->audience,
                'channels' => json_decode((string) $rule->channels, true) ?: [],
                'trigger' => (string) $rule->trigger,
                'status' => (string) $rule->status,
            ])
            ->all();
    }
}
