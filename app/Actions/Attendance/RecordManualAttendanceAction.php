<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class RecordManualAttendanceAction
{
    /**
     * @return array{status: string, message: string, attendanceRecordId: int|null}
     */
    public function execute(int $attendanceSessionId, int $meetingAttendeeId, int $actorUserId, ?Carbon $checkedInAt = null): array
    {
        $checkedInAt ??= now();

        $session = DB::table('attendance_sessions')
            ->where('id', $attendanceSessionId)
            ->first(['id', 'organization_id', 'meeting_id', 'status']);

        $attendee = DB::table('meeting_attendees')
            ->where('id', $meetingAttendeeId)
            ->first(['id', 'meeting_id', 'user_id', 'name']);

        if ($session === null || $attendee === null || (int) $session->meeting_id !== (int) $attendee->meeting_id) {
            return [
                'status' => 'invalid',
                'message' => 'Peserta tidak cocok dengan sesi absensi.',
                'attendanceRecordId' => null,
            ];
        }

        $canManage = DB::table('organization_members')
            ->where('organization_id', $session->organization_id)
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin', 'secretary', 'project_lead'])
            ->exists();

        if (! $canManage) {
            return [
                'status' => 'forbidden',
                'message' => 'User tidak punya akses mencatat absensi manual.',
                'attendanceRecordId' => null,
            ];
        }

        $existingRecord = DB::table('attendance_records')
            ->where('attendance_session_id', $session->id)
            ->where('meeting_attendee_id', $attendee->id)
            ->first(['id']);

        if ($existingRecord !== null) {
            return [
                'status' => 'duplicate',
                'message' => 'Peserta ini sudah tercatat hadir.',
                'attendanceRecordId' => (int) $existingRecord->id,
            ];
        }

        $user = $attendee->user_id === null
            ? null
            : DB::table('users')->where('id', $attendee->user_id)->first(['email']);

        $recordId = DB::table('attendance_records')->insertGetId([
            'attendance_session_id' => $session->id,
            'user_id' => $attendee->user_id,
            'meeting_attendee_id' => $attendee->id,
            'attendee_name' => (string) $attendee->name,
            'attendee_email' => $user?->email,
            'check_in_method' => 'manual',
            'checked_in_at' => $checkedInAt,
            'status' => 'present',
            'notes' => 'Dicatat manual oleh panitia.',
            'created_at' => $checkedInAt,
            'updated_at' => $checkedInAt,
        ]);

        DB::table('meeting_attendees')
            ->where('id', $attendee->id)
            ->update([
                'attendance_status' => 'present',
                'updated_at' => $checkedInAt,
            ]);

        return [
            'status' => 'checked_in',
            'message' => 'Absensi manual berhasil dicatat.',
            'attendanceRecordId' => (int) $recordId,
        ];
    }
}
