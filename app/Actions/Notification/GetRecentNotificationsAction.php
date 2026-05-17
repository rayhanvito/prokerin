<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class GetRecentNotificationsAction
{
    /**
     * @return array{
     *     unreadCount: int,
     *     recent: array<int, array{id: string, title: string, body: string, url: string|null, readAt: string|null, createdAt: string}>
     * }
     */
    public function execute(int $userId, int $limit = 5): array
    {
        $unreadCount = (int) DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->count();

        $rawNotifications = DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'type', 'data', 'read_at', 'created_at']);

        return [
            'unreadCount' => $unreadCount,
            'recent' => $rawNotifications
                ->map(static function (object $row): array {
                    $payload = json_decode((string) $row->data, true);
                    $payload = is_array($payload) ? $payload : [];

                    $title = (string) ($payload['title'] ?? $payload['jobLabel'] ?? $payload['projectName'] ?? class_basename((string) $row->type));
                    $body = (string) ($payload['body'] ?? $payload['reason'] ?? $payload['decision'] ?? '');

                    return [
                        'id' => (string) $row->id,
                        'title' => $title,
                        'body' => $body,
                        'url' => isset($payload['resourceUrl']) && is_string($payload['resourceUrl']) ? $payload['resourceUrl'] : null,
                        'readAt' => $row->read_at === null ? null : (string) $row->read_at,
                        'createdAt' => (string) $row->created_at,
                    ];
                })
                ->all(),
        ];
    }
}
