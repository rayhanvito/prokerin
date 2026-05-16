<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\SendTaskDeadlineRemindersAction;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class TaskDeadlineReminderController extends Controller
{
    public function store(
        Request $request,
        SendTaskDeadlineRemindersAction $sendTaskDeadlineReminders,
    ): RedirectResponse {
        $sentCount = $sendTaskDeadlineReminders->execute(
            actorUserId: (int) $request->user()->id,
            dueBefore: new DateTimeImmutable('+7 days'),
        );

        return back()->with('success', "{$sentCount} reminder deadline task masuk antrean notifikasi.");
    }
}
