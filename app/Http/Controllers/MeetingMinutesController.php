<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Meeting\PublishMeetingMinutesAction;
use App\Http\Requests\PublishMeetingMinutesRequest;
use Illuminate\Http\RedirectResponse;

final class MeetingMinutesController extends Controller
{
    public function update(
        PublishMeetingMinutesRequest $request,
        int $meeting,
        PublishMeetingMinutesAction $publishMinutes,
    ): RedirectResponse {
        $publishMinutes->execute((int) $request->user()->id, $meeting, $request->validated());

        return back()->with(
            'success',
            $request->validated()['publish'] === true
                ? 'Notulen berhasil dipublikasikan.'
                : 'Notulen berhasil disimpan sebagai draft.',
        );
    }
}
