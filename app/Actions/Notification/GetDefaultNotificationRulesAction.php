<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Domain\Notification\NotificationChannel;
use App\Domain\Notification\NotificationEvent;
use App\DTOs\Notification\NotificationRuleData;

final class GetDefaultNotificationRulesAction
{
    /**
     * @return array<int, NotificationRuleData>
     */
    public function execute(): array
    {
        return [
            new NotificationRuleData(
                event: NotificationEvent::TaskDeadlineReminder,
                audience: 'Assigned PIC',
                channels: [NotificationChannel::InApp, NotificationChannel::Email, NotificationChannel::WhatsApp],
                trigger: 'H-1 deadline',
            ),
            new NotificationRuleData(
                event: NotificationEvent::FinanceApprovalRequested,
                audience: 'Treasurer',
                channels: [NotificationChannel::InApp, NotificationChannel::WhatsApp],
                trigger: 'RAB submitted',
            ),
            new NotificationRuleData(
                event: NotificationEvent::MemberInviteSent,
                audience: 'Invitee',
                channels: [NotificationChannel::Email],
                trigger: 'Invitation created',
            ),
            new NotificationRuleData(
                event: NotificationEvent::ProposalReviewRequested,
                audience: 'Secretary and organization admin',
                channels: [NotificationChannel::InApp, NotificationChannel::WhatsApp],
                trigger: 'Proposal moved to review',
            ),
            new NotificationRuleData(
                event: NotificationEvent::LpjReviewRequested,
                audience: 'Secretary and project lead',
                channels: [NotificationChannel::InApp, NotificationChannel::WhatsApp],
                trigger: 'LPJ checklist completed',
            ),
            new NotificationRuleData(
                event: NotificationEvent::MeetingAlert,
                audience: 'Meeting attendees',
                channels: [NotificationChannel::WhatsApp],
                trigger: 'H-7 meeting',
            ),
            new NotificationRuleData(
                event: NotificationEvent::ApprovalWorkflowStepAssigned,
                audience: 'Active workflow approver',
                channels: [NotificationChannel::InApp, NotificationChannel::WhatsApp],
                trigger: 'Workflow starts or advances',
            ),
        ];
    }
}
