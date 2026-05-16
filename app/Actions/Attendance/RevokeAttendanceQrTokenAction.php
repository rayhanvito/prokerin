<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RevokeAttendanceQrTokenAction
{
    public function execute(int $actorUserId, int $tokenId): void
    {
        $token = DB::table('attendance_qr_tokens')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_qr_tokens.attendance_session_id')
            ->where('attendance_qr_tokens.id', $tokenId)
            ->select([
                'attendance_qr_tokens.id',
                'attendance_qr_tokens.revoked_at',
                'attendance_sessions.organization_id',
            ])
            ->first();

        if ($token === null) {
            throw new NotFoundHttpException('Token tidak ditemukan.');
        }

        $this->guardManager($actorUserId, (int) $token->organization_id);

        DB::table('attendance_qr_tokens')
            ->where('id', $token->id)
            ->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function guardManager(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary', 'project_lead'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Tidak punya akses untuk merevoke token QR.');
        }
    }
}
