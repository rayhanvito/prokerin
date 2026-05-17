<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class CheckInAttendanceQrAction
{
    /**
     * @return array{status: string, message: string, attendanceRecordId: int|null}
     */
    public function execute(
        string $token,
        int $userId,
        ?Carbon $checkedInAt = null,
        string $method = 'qr',
    ): array {
        $checkedInAt ??= now();
        $tokenHash = hash('sha256', $token);

        $qrToken = DB::table('attendance_qr_tokens')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_qr_tokens.attendance_session_id')
            ->where('attendance_qr_tokens.token_hash', $tokenHash)
            ->whereNull('attendance_qr_tokens.revoked_at')
            ->select([
                'attendance_qr_tokens.id',
                'attendance_qr_tokens.expires_at',
                'attendance_sessions.id as session_id',
                'attendance_sessions.organization_id',
                'attendance_sessions.meeting_id',
                'attendance_sessions.status',
            ])
            ->first();

        if ($qrToken === null || Carbon::parse((string) $qrToken->expires_at)->lt($checkedInAt)) {
            return [
                'status' => 'expired',
                'message' => 'Token QR sudah tidak berlaku.',
                'attendanceRecordId' => null,
            ];
        }

        if ($qrToken->status !== 'open') {
            return [
                'status' => 'closed',
                'message' => 'Sesi absensi sudah ditutup.',
                'attendanceRecordId' => null,
            ];
        }

        $membershipExists = DB::table('organization_members')
            ->where('organization_id', $qrToken->organization_id)
            ->where('user_id', $userId)
            ->exists();

        if (! $membershipExists) {
            return [
                'status' => 'forbidden',
                'message' => 'User tidak terdaftar di organisasi sesi absensi.',
                'attendanceRecordId' => null,
            ];
        }

        $user = DB::table('users')->where('id', $userId)->first(['id', 'name', 'email']);
        $meetingAttendee = $qrToken->meeting_id === null
            ? null
            : DB::table('meeting_attendees')
                ->where('meeting_id', $qrToken->meeting_id)
                ->where('user_id', $userId)
                ->first(['id']);

        $existingRecord = DB::table('attendance_records')
            ->where('attendance_session_id', $qrToken->session_id)
            ->where(function ($query) use ($userId, $meetingAttendee): void {
                $query->where('user_id', $userId);

                if ($meetingAttendee !== null) {
                    $query->orWhere('meeting_attendee_id', $meetingAttendee->id);
                }
            })
            ->first(['id']);

        if ($existingRecord !== null) {
            return [
                'status' => 'duplicate',
                'message' => 'Absensi user ini sudah tercatat.',
                'attendanceRecordId' => (int) $existingRecord->id,
            ];
        }

        $recordId = DB::table('attendance_records')->insertGetId([
            'attendance_session_id' => $qrToken->session_id,
            'user_id' => $userId,
            'meeting_attendee_id' => $meetingAttendee?->id,
            'attendee_name' => (string) $user->name,
            'attendee_email' => (string) $user->email,
            'check_in_method' => $this->normalizeMethod($method),
            'checked_in_at' => $checkedInAt,
            'status' => 'present',
            'created_at' => $checkedInAt,
            'updated_at' => $checkedInAt,
        ]);

        if ($meetingAttendee !== null) {
            DB::table('meeting_attendees')
                ->where('id', $meetingAttendee->id)
                ->update([
                    'attendance_status' => 'present',
                    'updated_at' => $checkedInAt,
                ]);
        }

        DB::table('attendance_qr_tokens')
            ->where('id', $qrToken->id)
            ->update([
                'last_used_at' => $checkedInAt,
                'updated_at' => $checkedInAt,
            ]);

        return [
            'status' => 'checked_in',
            'message' => 'Absensi berhasil dicatat.',
            'attendanceRecordId' => (int) $recordId,
        ];
    }

    private function normalizeMethod(string $method): string
    {
        return $method === 'qr_camera' ? 'qr_camera' : 'qr';
    }
}
