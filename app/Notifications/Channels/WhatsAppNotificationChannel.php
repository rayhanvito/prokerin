<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Jobs\SendWhatsAppReminderJob;
use Illuminate\Notifications\Notification;

final class WhatsAppNotificationChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $recipientNumber = $this->recipientNumber($notifiable);

        if ($recipientNumber === null) {
            return;
        }

        SendWhatsAppReminderJob::dispatch(
            organizationId: null,
            userId: property_exists($notifiable, 'id') ? (int) $notifiable->id : null,
            recipientNumber: $recipientNumber,
            messageType: class_basename($notification),
            message: (string) $notification->toWhatsApp($notifiable),
        )->onQueue('notifications');
    }

    private function recipientNumber(object $notifiable): ?string
    {
        if (method_exists($notifiable, 'routeNotificationForWhatsApp')) {
            $number = $notifiable->routeNotificationForWhatsApp();

            return filled($number) ? (string) $number : null;
        }

        $number = $notifiable->whatsapp_number ?? null;

        return filled($number) ? (string) $number : null;
    }
}
