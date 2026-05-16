<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notification\SendMeetingWhatsAppAlertsAction;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class MeetingWhatsAppAlertController extends Controller
{
    public function store(Request $request, SendMeetingWhatsAppAlertsAction $sendMeetingWhatsAppAlerts): RedirectResponse
    {
        $sentCount = $sendMeetingWhatsAppAlerts->execute(
            actorUserId: (int) $request->user()->id,
            startsBefore: new DateTimeImmutable('+7 days'),
        );

        return back()->with('success', "{$sentCount} reminder rapat WhatsApp masuk antrean.");
    }
}
