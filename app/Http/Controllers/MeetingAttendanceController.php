<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Meeting\RecordMeetingAttendanceAction;
use App\Http\Requests\RecordMeetingAttendanceRequest;
use Illuminate\Http\RedirectResponse;

final class MeetingAttendanceController extends Controller
{
    public function update(
        RecordMeetingAttendanceRequest $request,
        int $attendee,
        RecordMeetingAttendanceAction $recordAttendance,
    ): RedirectResponse {
        $recordAttendance->execute(
            (int) $request->user()->id,
            $attendee,
            (string) $request->validated()['attendance_status'],
        );

        return back()->with('success', 'Kehadiran berhasil diperbarui.');
    }
}
