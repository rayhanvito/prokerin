<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Domain\Organization\OrganizationRole;
use App\Domain\Task\TaskStatus;
use App\Jobs\SendWhatsAppReminderJob;
use App\Models\User;
use App\Notifications\TaskDeadlineReminderNotification;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class SendTaskDeadlineRemindersAction
{
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

        $whatsAppEnabledOrganizationIds = $this->whatsAppEnabledOrganizationIds($organizationIds);

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

            if (
                $user !== null
                && filled($user->whatsapp_number)
                && in_array((int) $task->organization_id, $whatsAppEnabledOrganizationIds, true)
            ) {
                SendWhatsAppReminderJob::dispatch(
                    organizationId: (int) $task->organization_id,
                    userId: (int) $user->id,
                    recipientNumber: (string) $user->whatsapp_number,
                    messageType: 'task_deadline_reminder',
                    message: (new TaskDeadlineReminderNotification(
                        taskTitle: (string) $task->title,
                        projectName: (string) $task->project_name,
                        dueAt: (string) $task->due_at,
                    ))->toWhatsApp($user),
                )->onQueue('notifications');
            }
        }

        return $tasks->count();
    }

    /**
     * @param  array<int, int>  $organizationIds
     * @return array<int, int>
     */
    private function whatsAppEnabledOrganizationIds(array $organizationIds): array
    {
        return DB::table('notification_rules')
            ->whereIn('organization_id', $organizationIds)
            ->where('event', 'task_deadline_reminder')
            ->get(['organization_id', 'channels'])
            ->filter(static function (object $rule): bool {
                $channels = json_decode((string) $rule->channels, true) ?: [];

                return in_array('whatsapp', $channels, true);
            })
            ->map(static fn (object $rule): int => (int) $rule->organization_id)
            ->values()
            ->all();
    }
}
