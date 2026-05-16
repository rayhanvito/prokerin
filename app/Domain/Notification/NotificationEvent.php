<?php

declare(strict_types=1);

namespace App\Domain\Notification;

enum NotificationEvent: string
{
    case TaskDeadlineReminder = 'task_deadline_reminder';
    case FinanceApprovalRequested = 'finance_approval_requested';
    case MemberInviteSent = 'member_invite_sent';
    case ProposalReviewRequested = 'proposal_review_requested';
    case LpjReviewRequested = 'lpj_review_requested';
    case MeetingAlert = 'meeting_alert';

    public function label(): string
    {
        return match ($this) {
            self::TaskDeadlineReminder => 'Task deadline reminder',
            self::FinanceApprovalRequested => 'Finance approval requested',
            self::MemberInviteSent => 'Member invite sent',
            self::ProposalReviewRequested => 'Proposal review requested',
            self::LpjReviewRequested => 'LPJ review requested',
            self::MeetingAlert => 'Meeting alert',
        };
    }
}
