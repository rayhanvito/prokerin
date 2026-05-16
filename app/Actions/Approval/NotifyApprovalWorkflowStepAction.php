<?php

declare(strict_types=1);

namespace App\Actions\Approval;

use App\Actions\Notification\QueueWhatsAppNotificationAction;
use App\Domain\Notification\NotificationEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class NotifyApprovalWorkflowStepAction
{
    public function __construct(private QueueWhatsAppNotificationAction $queueWhatsAppNotification) {}

    public function execute(int $instanceId): void
    {
        $step = DB::table('approval_instances')
            ->join('approval_workflow_definitions', 'approval_workflow_definitions.id', '=', 'approval_instances.workflow_definition_id')
            ->join('approval_step_records', function ($join): void {
                $join->on('approval_step_records.instance_id', '=', 'approval_instances.id')
                    ->on('approval_step_records.step_order', '=', 'approval_instances.current_step');
            })
            ->leftJoin('users', 'users.id', '=', 'approval_step_records.approver_id')
            ->where('approval_instances.id', $instanceId)
            ->where('approval_instances.status', 'pending')
            ->where('approval_step_records.decision', 'pending')
            ->first([
                'approval_instances.id',
                'approval_instances.subject_type',
                'approval_instances.subject_id',
                'approval_instances.current_step',
                'approval_workflow_definitions.organization_id',
                'approval_workflow_definitions.workflow_type',
                'approval_step_records.approver_id',
                'users.name as approver_name',
            ]);

        if ($step === null || $step->approver_id === null) {
            return;
        }

        $message = sprintf(
            'Approval %s step %d menunggu keputusan Anda di Prokerin.',
            (string) $step->workflow_type,
            (int) $step->current_step,
        );

        if ($this->eventAllowsChannel((int) $step->organization_id, 'in_app')) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => NotificationEvent::ApprovalWorkflowStepAssigned->value,
                'notifiable_type' => User::class,
                'notifiable_id' => (int) $step->approver_id,
                'data' => json_encode([
                    'title' => 'Approval workflow menunggu keputusan',
                    'message' => $message,
                    'approval_instance_id' => (int) $step->id,
                    'workflow_type' => (string) $step->workflow_type,
                    'subject_type' => (string) $step->subject_type,
                    'subject_id' => (int) $step->subject_id,
                    'step' => (int) $step->current_step,
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->queueWhatsAppNotification->execute(
            organizationId: (int) $step->organization_id,
            event: NotificationEvent::ApprovalWorkflowStepAssigned,
            userIds: [(int) $step->approver_id],
            messageType: NotificationEvent::ApprovalWorkflowStepAssigned->value,
            message: $message,
        );
    }

    private function eventAllowsChannel(int $organizationId, string $channel): bool
    {
        $channels = DB::table('notification_rules')
            ->where('organization_id', $organizationId)
            ->where('event', NotificationEvent::ApprovalWorkflowStepAssigned->value)
            ->value('channels');

        if ($channels === null) {
            return false;
        }

        return in_array($channel, json_decode((string) $channels, true) ?: [], true);
    }
}
