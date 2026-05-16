<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Domain\Notification\NotificationEvent;
use App\Domain\Organization\OrganizationRole;
use App\Domain\Task\TaskStatus;
use App\Models\User;
use App\Notifications\TaskDeadlineReminderNotification;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final readonly class SendTaskDeadlineRemindersAction
{
    public function __construct(
        private QueueWhatsAppNotificationAction $queueWhatsAppNotification,
    ) {}

    public function execute(int $actorUserId, DateTimeImmutable $dueBefore): int
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
                OrganizationRole::Secretary->value,
            ])
            ->pluck('organization_id')
            ->map(static fn (int|string $id): int => (int) $id)
            ->all();

        if ($organizationIds === []) {
            return 0;
        }

        $tasks = DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->whereIn('projects.organization_id', $organizationIds)
            ->whereNotNull('project_tasks.pic_user_id')
            ->where('project_tasks.status', '!=', TaskStatus::Done->value)
            ->whereDate('project_tasks.due_at', '<=', $dueBefore->format('Y-m-d'))
            ->select([
                'project_tasks.title',
                'project_tasks.due_at',
                'project_tasks.pic_user_id',
                'projects.organization_id',
                'projects.name as project_name',
            ])
            ->get();

        foreach ($tasks as $task) {
            $user = User::query()->find((int) $task->pic_user_id);

            $user?->notify(new TaskDeadlineReminderNotification(
                taskTitle: (string) $task->title,
                projectName: (string) $task->project_name,
                dueAt: (string) $task->due_at,
            ));

            if ($user !== null) {
                $this->queueWhatsAppNotification->execute(
                    organizationId: (int) $task->organization_id,
                    event: NotificationEvent::TaskDeadlineReminder,
                    userIds: [(int) $user->id],
                    messageType: NotificationEvent::TaskDeadlineReminder->value,
                    message: (new TaskDeadlineReminderNotification(
                        taskTitle: (string) $task->title,
                        projectName: (string) $task->project_name,
                        dueAt: (string) $task->due_at,
                    ))->toWhatsApp($user),
                );
            }
        }

        return $tasks->count();
    }
}
