<?php

declare(strict_types=1);

namespace App\Actions\Meeting;

use App\Domain\Meeting\AttendanceStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RecordMeetingAttendanceAction
{
    public function execute(int $actorUserId, int $attendeeId, string $status): void
    {
        $valid = array_map(static fn (AttendanceStatus $case): string => $case->value, AttendanceStatus::cases());

        if (! in_array($status, $valid, true)) {
            throw new AuthorizationException('Attendance status tidak valid.');
        }

        $attendee = DB::table('meeting_attendees')
            ->join('meetings', 'meetings.id', '=', 'meeting_attendees.meeting_id')
            ->where('meeting_attendees.id', $attendeeId)
            ->first([
                'meeting_attendees.id',
                'meeting_attendees.meeting_id',
                'meetings.organization_id',
            ]);

        if ($attendee === null) {
            throw new NotFoundHttpException('Attendee not found.');
        }

        $this->guardActor($actorUserId, (int) $attendee->organization_id);

        DB::table('meeting_attendees')
            ->where('id', $attendee->id)
            ->update([
                'attendance_status' => $status,
                'updated_at' => now(),
            ]);
    }

    private function guardActor(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Hanya owner, admin, atau sekretaris yang dapat mengubah kehadiran.');
        }
    }
}
