<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Attendance\IssueAttendanceQrTokenAction;
use App\Actions\Attendance\RevokeAttendanceQrTokenAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AttendanceQrTokenController extends Controller
{
    public function store(
        Request $request,
        int $session,
        IssueAttendanceQrTokenAction $issueToken,
    ): RedirectResponse {
        $result = $issueToken->execute((int) $request->user()->id, $session);

        return back()->with('success', sprintf(
            'QR token baru di-generate (berlaku sampai %s).',
            $result['expires_at'],
        ))->with('attendanceQrToken', [
            'sessionId' => $session,
            'tokenId' => $result['token_id'],
            'plainToken' => $result['plain_token'],
            'expiresAt' => $result['expires_at'],
        ]);
    }

    public function destroy(
        Request $request,
        int $token,
        RevokeAttendanceQrTokenAction $revokeToken,
    ): RedirectResponse {
        $revokeToken->execute((int) $request->user()->id, $token);

        return back()->with('success', 'QR token berhasil di-revoke.');
    }
}
