<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Attendance\RecordManualAttendanceAction;
use App\Http\Requests\RecordManualAttendanceRequest;
use Illuminate\Http\RedirectResponse;

final class ManualAttendanceController extends Controller
{
    public function store(
        RecordManualAttendanceRequest $request,
        int $session,
        RecordManualAttendanceAction $recordManualAttendance,
    ): RedirectResponse {
        $result = $recordManualAttendance->execute(
            attendanceSessionId: $session,
            meetingAttendeeId: (int) $request->validated('meeting_attendee_id'),
            actorUserId: (int) $request->user()->id,
        );

        $flashKey = $result['status'] === 'checked_in' ? 'success' : 'error';

        if ($result['status'] === 'duplicate') {
            $flashKey = 'status';
        }

        return back()->with($flashKey, $result['message']);
    }
}
