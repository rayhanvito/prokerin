<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppNotificationChannel;
use App\Notifications\Concerns\SendsWebPushNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

final class ProposalApprovalDecidedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsWebPushNotifications;

    public function __construct(
        public readonly string $projectName,
        public readonly string $decision, // 'approved' | 'revision_requested'
        public readonly string $approverName,
        public readonly ?string $resourceUrl = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->withWebPush(['database', 'mail', WhatsAppNotificationChannel::class]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $headline = $this->decision === 'approved'
            ? "Proposal proker {$this->projectName} disetujui"
            : "Proposal proker {$this->projectName} perlu revisi";

        $message = (new MailMessage)
            ->subject($headline)
            ->line($headline.'.')
            ->line("Status diberikan oleh {$this->approverName}.");

        if ($this->resourceUrl !== null) {
            $message->action('Buka Proposal', $this->resourceUrl);
        }

        return $message;
    }

    public function toWhatsApp(object $notifiable): string
    {
        return $this->decision === 'approved'
            ? "Proposal {$this->projectName} disetujui oleh {$this->approverName}."
            : "Proposal {$this->projectName} dikembalikan untuk revisi oleh {$this->approverName}.";
    }

    public function toWebPush(object $notifiable, ?Notification $notification = null): WebPushMessage
    {
        $headline = $this->decision === 'approved'
            ? 'Proposal disetujui'
            : 'Proposal perlu revisi';

        return $this->webPushMessage(
            title: $headline,
            body: "{$this->projectName} oleh {$this->approverName}.",
            url: $this->resourceUrl,
        );
    }

    /**
     * @return array{projectName: string, decision: string, approverName: string, resourceUrl: string|null}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'projectName' => $this->projectName,
            'decision' => $this->decision,
            'approverName' => $this->approverName,
            'resourceUrl' => $this->resourceUrl,
        ];
    }
}
