<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Meeting\QueueMeetingMinutesExportAction;
use App\Http\Requests\QueueMeetingMinutesExportRequest;
use Illuminate\Http\RedirectResponse;

final class MeetingMinutesExportController extends Controller
{
    public function store(
        QueueMeetingMinutesExportRequest $request,
        int $meeting,
        QueueMeetingMinutesExportAction $queueExport,
    ): RedirectResponse {
        $queueExport->execute(
            (int) $request->user()->id,
            $meeting,
            (string) $request->validated()['format'],
        );

        return back()->with('success', 'Export notulen berhasil di-queue.');
    }
}
