<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Domain\Notification\NotificationEvent;
use App\Domain\Organization\OrganizationRole;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final readonly class SendMeetingWhatsAppAlertsAction
{
    public function __construct(
        private QueueWhatsAppNotificationAction $queueWhatsAppNotification,
    ) {}

    public function execute(int $actorUserId, DateTimeImmutable $startsBefore): int
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
                OrganizationRole::Secretary->value,
            ])
            ->pluck('organization_id')
            ->map(static fn (int|string $id): int => (int) $id)
            ->all();

        if ($organizationIds === []) {
            return 0;
        }

        $meetings = DB::table('meetings')
            ->whereIn('organization_id', $organizationIds)
            ->where('status', 'planned')
            ->whereBetween('starts_at', [now(), $startsBefore->format('Y-m-d H:i:s')])
            ->get(['id', 'organization_id', 'title', 'starts_at', 'location']);

        $sentCount = 0;

        foreach ($meetings as $meeting) {
            $userIds = DB::table('meeting_attendees')
                ->where('meeting_id', $meeting->id)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->map(static fn (int|string $id): int => (int) $id)
                ->all();

            $sentCount += $this->queueWhatsAppNotification->execute(
                organizationId: (int) $meeting->organization_id,
                event: NotificationEvent::MeetingAlert,
                userIds: $userIds,
                messageType: NotificationEvent::MeetingAlert->value,
                message: sprintf(
                    'Reminder Prokerin: rapat %s dijadwalkan pada %s di %s.',
                    (string) $meeting->title,
                    (string) $meeting->starts_at,
                    (string) ($meeting->location ?? 'lokasi belum ditentukan'),
                ),
            );
        }

        return $sentCount;
    }
}
