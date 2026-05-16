<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class EventRegistrationConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $participantName,
        public readonly string $projectName,
        public readonly string $organizationName,
        public readonly string $status,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Konfirmasi registrasi event Prokerin')
            ->greeting("Halo {$this->participantName},")
            ->line("Registrasi Anda untuk {$this->projectName} oleh {$this->organizationName} berhasil diterima.")
            ->line("Status registrasi saat ini: {$this->status}.")
            ->line('Simpan email ini sebagai bukti registrasi awal.');
    }

    /**
     * @return array{participantName: string, projectName: string, organizationName: string, status: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'participantName' => $this->participantName,
            'projectName' => $this->projectName,
            'organizationName' => $this->organizationName,
            'status' => $this->status,
        ];
    }
}
