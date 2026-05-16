<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TaskDeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $taskTitle,
        public readonly string $projectName,
        public readonly string $dueAt,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reminder deadline task Prokerin')
            ->line("Task {$this->taskTitle} untuk {$this->projectName} memiliki deadline {$this->dueAt}.")
            ->action('Buka Task Kanban', route('tasks.kanban'));
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "Reminder Prokerin: task {$this->taskTitle} untuk {$this->projectName} deadline {$this->dueAt}.";
    }

    /**
     * @return array{taskTitle: string, projectName: string, dueAt: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'taskTitle' => $this->taskTitle,
            'projectName' => $this->projectName,
            'dueAt' => $this->dueAt,
        ];
    }
}
