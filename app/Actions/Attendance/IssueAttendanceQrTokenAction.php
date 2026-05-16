<?php

declare(strict_types=1);

namespace App\Actions\Attendance;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class IssueAttendanceQrTokenAction
{
    /**
     * @return array{token_id: int, plain_token: string, expires_at: string}
     */
    public function execute(int $actorUserId, int $sessionId, ?int $expiresInMinutes = null): array
    {
        $session = DB::table('attendance_sessions')
            ->where('id', $sessionId)
            ->first(['id', 'organization_id', 'status']);

        if ($session === null) {
            throw new NotFoundHttpException('Attendance session not found.');
        }

        $this->guardManager($actorUserId, (int) $session->organization_id);

        // Revoke previous active tokens for this session
        DB::table('attendance_qr_tokens')
            ->where('attendance_session_id', $session->id)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);

        $plainToken = (string) Str::random(40);
        $tokenHash = hash('sha256', $plainToken);
        $expiresAt = now()->addMinutes($expiresInMinutes ?? 60);
        $now = now();

        $tokenId = (int) DB::table('attendance_qr_tokens')->insertGetId([
            'attendance_session_id' => $session->id,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'token_id' => $tokenId,
            'plain_token' => $plainToken,
            'expires_at' => $expiresAt->toDateTimeString(),
        ];
    }

    private function guardManager(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary', 'project_lead'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Tidak punya akses untuk mengelola QR absensi.');
        }
    }
}
