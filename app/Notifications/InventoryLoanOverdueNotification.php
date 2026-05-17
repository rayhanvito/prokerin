<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Notifications\Concerns\SendsWebPushNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

final class InventoryLoanOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsWebPushNotifications;

    public function __construct(
        public readonly string $itemName,
        public readonly string $expectedReturnAt,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->withWebPush(['database', 'mail']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Peminjaman inventaris melewati jadwal')
            ->line("Inventaris {$this->itemName} melewati jadwal pengembalian {$this->expectedReturnAt}.")
            ->action('Buka Inventaris', route('inventory.index'));
    }

    public function toWebPush(object $notifiable, ?Notification $notification = null): WebPushMessage
    {
        return $this->webPushMessage(
            title: 'Peminjaman inventaris overdue',
            body: "{$this->itemName} melewati jadwal pengembalian.",
            url: route('inventory.index', absolute: false),
        );
    }

    /**
     * @return array{itemName: string, expectedReturnAt: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'itemName' => $this->itemName,
            'expectedReturnAt' => $this->expectedReturnAt,
        ];
    }
}
