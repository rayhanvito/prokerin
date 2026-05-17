<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\MarkAllNotificationsReadAction;
use App\Actions\Notification\MarkNotificationReadAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class NotificationReadController extends Controller
{
    public function update(
        Request $request,
        string $notification,
        MarkNotificationReadAction $markRead,
    ): RedirectResponse {
        $markRead->execute(
            actorUserId: (int) $request->user()->id,
            notificationId: $notification,
        );

        return back()->with('success', 'Notifikasi ditandai dibaca.');
    }

    public function readAll(
        Request $request,
        MarkAllNotificationsReadAction $markAllRead,
    ): RedirectResponse {
        $markAllRead->execute((int) $request->user()->id);

        return back()->with('success', 'Semua notifikasi ditandai dibaca.');
    }
}
