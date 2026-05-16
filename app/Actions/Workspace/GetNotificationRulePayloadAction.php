<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetNotificationRulePayloadAction
{
    /**
     * @return array{notificationRules: array<int, array{event: string, label: string, audience: string, channels: array<int, string>, trigger: string, status: string}>, whatsappLogs: array<int, array{id: int, recipient: string, messageType: string, status: string, sentAt: string|null, failedAt: string|null}>}
     */
    public function execute(int $actorUserId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->pluck('organization_id')
            ->all();

        return [
            'notificationRules' => $this->rules($organizationIds),
            'whatsappLogs' => $organizationIds === [] ? [] : $this->whatsAppLogs($organizationIds),
        ];
    }

    /**
     * @param  array<int, int|string>  $organizationIds
     * @return array<int, array{event: string, label: string, audience: string, channels: array<int, string>, trigger: string, status: string}>
     */
    private function rules(array $organizationIds): array
    {
        return DB::table('notification_rules')
            ->when($organizationIds !== [], fn ($query) => $query->whereIn('organization_id', $organizationIds))
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

    /**
     * @param  array<int, int|string>  $organizationIds
     * @return array<int, array{id: int, recipient: string, messageType: string, status: string, sentAt: string|null, failedAt: string|null}>
     */
    private function whatsAppLogs(array $organizationIds): array
    {
        return DB::table('whatsapp_delivery_logs')
            ->whereIn('organization_id', $organizationIds)
            ->orderByDesc('id')
            ->limit(10)
            ->get([
                'id',
                'recipient_number',
                'message_type',
                'status',
                'sent_at',
                'failed_at',
            ])
            ->map(static fn (object $log): array => [
                'id' => (int) $log->id,
                'recipient' => (string) $log->recipient_number,
                'messageType' => (string) $log->message_type,
                'status' => (string) $log->status,
                'sentAt' => $log->sent_at === null ? null : (string) $log->sent_at,
                'failedAt' => $log->failed_at === null ? null : (string) $log->failed_at,
            ])
            ->all();
    }
}
