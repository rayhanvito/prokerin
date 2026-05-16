<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class GetQrAttendancePayloadAction
{
    /**
     * @return array{
     *     metrics: array<int, array{label: string, value: string, note: string}>,
     *     sessions: array<int, array{id: int, title: string, project: string, meeting: string, startsAt: string, status: string, expiresAt: string|null, attendeeCount: int, presentCount: int, qrCount: int, manualCount: int, activeTokenId: int|null, canManageQr: bool}>,
     *     recentRecords: array<int, array{id: int, attendeeName: string, sessionTitle: string, method: string, checkedInAt: string, status: string}>
     * }
     */
    public function execute(int $userId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->pluck('organization_id');

        $manageRoles = ['organization_owner', 'organization_admin', 'secretary', 'project_lead'];
        $manageOrgIds = DB::table('organization_members')
            ->where('user_id', $userId)
            ->whereIn('role', $manageRoles)
            ->pluck('organization_id')
            ->all();

        $sessionRows = DB::table('attendance_sessions')
            ->leftJoin('projects', 'projects.id', '=', 'attendance_sessions.project_id')
            ->leftJoin('meetings', 'meetings.id', '=', 'attendance_sessions.meeting_id')
            ->leftJoin('attendance_qr_tokens', function ($join): void {
                $join->on('attendance_qr_tokens.attendance_session_id', '=', 'attendance_sessions.id')
                    ->whereNull('attendance_qr_tokens.revoked_at');
            })
            ->whereIn('attendance_sessions.organization_id', $organizationIds)
            ->orderByDesc('attendance_sessions.starts_at')
            ->limit(10)
            ->get([
                'attendance_sessions.id',
                'attendance_sessions.organization_id',
                'attendance_sessions.title',
                'attendance_sessions.starts_at',
                'attendance_sessions.status',
                'projects.name as project_name',
                'meetings.title as meeting_title',
                'attendance_qr_tokens.id as token_id',
                'attendance_qr_tokens.expires_at',
            ]);

        $sessionIds = $sessionRows->pluck('id');

        $recordCounts = DB::table('attendance_records')
            ->whereIn('attendance_session_id', $sessionIds)
            ->selectRaw('attendance_session_id, count(*) as present_count, sum(case when check_in_method = ? then 1 else 0 end) as qr_count, sum(case when check_in_method = ? then 1 else 0 end) as manual_count', ['qr', 'manual'])
            ->groupBy('attendance_session_id')
            ->get()
            ->keyBy('attendance_session_id');

        $attendeeCounts = DB::table('attendance_sessions')
            ->join('meeting_attendees', 'meeting_attendees.meeting_id', '=', 'attendance_sessions.meeting_id')
            ->whereIn('attendance_sessions.id', $sessionIds)
            ->selectRaw('attendance_sessions.id as session_id, count(*) as attendee_count')
            ->groupBy('attendance_sessions.id')
            ->get()
            ->keyBy('session_id');

        $recentRecords = DB::table('attendance_records')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_records.attendance_session_id')
            ->whereIn('attendance_sessions.organization_id', $organizationIds)
            ->orderByDesc('attendance_records.checked_in_at')
            ->limit(8)
            ->get([
                'attendance_records.id',
                'attendance_records.attendee_name',
                'attendance_records.check_in_method',
                'attendance_records.checked_in_at',
                'attendance_records.status',
                'attendance_sessions.title as session_title',
            ]);

        $openSessions = $sessionRows->filter(static fn (object $session): bool => $session->status === 'open')->count();
        $expiredQr = $sessionRows->filter(static fn (object $session): bool => $session->expires_at !== null && Carbon::parse((string) $session->expires_at)->isPast())->count();
        $presentTotal = $recordCounts->sum(static fn (object $counts): int => (int) $counts->present_count);

        return [
            'metrics' => [
                [
                    'label' => 'Sesi aktif',
                    'value' => (string) $openSessions,
                    'note' => 'QR masih bisa dipakai check-in',
                ],
                [
                    'label' => 'Hadir tercatat',
                    'value' => (string) $presentTotal,
                    'note' => 'Gabungan QR dan manual',
                ],
                [
                    'label' => 'QR expired',
                    'value' => (string) $expiredQr,
                    'note' => 'Perlu regenerate sebelum dipakai',
                ],
            ],
            'sessions' => $sessionRows
                ->map(function (object $session) use ($recordCounts, $attendeeCounts, $manageOrgIds): array {
                    $records = $recordCounts->get($session->id);
                    $attendees = $attendeeCounts->get($session->id);

                    return [
                        'id' => (int) $session->id,
                        'title' => (string) $session->title,
                        'project' => (string) ($session->project_name ?? 'Agenda organisasi'),
                        'meeting' => (string) ($session->meeting_title ?? 'Tidak terhubung rapat'),
                        'startsAt' => (string) $session->starts_at,
                        'status' => (string) $session->status,
                        'expiresAt' => $session->expires_at === null ? null : (string) $session->expires_at,
                        'attendeeCount' => (int) ($attendees->attendee_count ?? 0),
                        'presentCount' => (int) ($records->present_count ?? 0),
                        'qrCount' => (int) ($records->qr_count ?? 0),
                        'manualCount' => (int) ($records->manual_count ?? 0),
                        'activeTokenId' => $session->token_id === null ? null : (int) $session->token_id,
                        'canManageQr' => in_array((int) $session->organization_id, $manageOrgIds, true),
                    ];
                })
                ->all(),
            'recentRecords' => $recentRecords
                ->map(static fn (object $record): array => [
                    'id' => (int) $record->id,
                    'attendeeName' => (string) $record->attendee_name,
                    'sessionTitle' => (string) $record->session_title,
                    'method' => (string) $record->check_in_method,
                    'checkedInAt' => (string) $record->checked_in_at,
                    'status' => (string) $record->status,
                ])
                ->all(),
        ];
    }
}
