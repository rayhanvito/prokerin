<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Attendance\CheckInAttendanceQrAction;
use App\Http\Requests\CheckInAttendanceQrRequest;
use Illuminate\Http\RedirectResponse;

final class AttendanceQrCheckInController extends Controller
{
    public function store(CheckInAttendanceQrRequest $request, CheckInAttendanceQrAction $checkIn): RedirectResponse
    {
        $result = $checkIn->execute(
            token: (string) $request->validated('token'),
            userId: (int) $request->user()->id,
        );

        $flashKey = $result['status'] === 'checked_in' ? 'success' : 'error';

        if ($result['status'] === 'duplicate') {
            $flashKey = 'status';
        }

        return back()->with($flashKey, $result['message']);
    }
}
