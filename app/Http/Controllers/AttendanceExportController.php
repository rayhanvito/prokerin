<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AttendanceExportController extends Controller
{
    public function show(Request $request, int $session): Response
    {
        $sessionRow = DB::table('attendance_sessions')
            ->leftJoin('organizations', 'organizations.id', '=', 'attendance_sessions.organization_id')
            ->where('attendance_sessions.id', $session)
            ->first([
                'attendance_sessions.id',
                'attendance_sessions.organization_id',
                'attendance_sessions.title',
                'attendance_sessions.starts_at',
                'organizations.name as organization_name',
            ]);

        if ($sessionRow === null) {
            throw new NotFoundHttpException('Attendance session not found.');
        }

        $userId = (int) $request->user()->id;

        $role = (string) DB::table('organization_members')
            ->where('user_id', $userId)
            ->where('organization_id', $sessionRow->organization_id)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary', 'project_lead'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Tidak punya akses ke export absensi.');
        }

        $records = DB::table('attendance_records')
            ->where('attendance_session_id', $sessionRow->id)
            ->orderBy('checked_in_at')
            ->get([
                'attendee_name',
                'attendee_email',
                'check_in_method',
                'checked_in_at',
                'status',
                'notes',
            ]);

        $filename = sprintf(
            'attendance-%s-%s.csv',
            (string) $sessionRow->id,
            now()->format('Ymd-His'),
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            'Cache-Control' => 'private, no-store, max-age=0',
        ];

        $csv = "\xEF\xBB\xBF"."name,email,method,checked_in_at,status,notes\r\n";

        foreach ($records as $record) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s\r\n",
                $this->escape((string) $record->attendee_name),
                $this->escape((string) ($record->attendee_email ?? '')),
                $this->escape((string) $record->check_in_method),
                $this->escape((string) $record->checked_in_at),
                $this->escape((string) $record->status),
                $this->escape((string) ($record->notes ?? '')),
            );
        }

        return response($csv, 200, $headers);
    }

    private function escape(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }
}
