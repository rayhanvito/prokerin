<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Notification\GetDefaultNotificationRulesAction;
use App\Domain\Notification\NotificationChannel;
use App\Domain\Notification\NotificationEvent;
use PHPUnit\Framework\TestCase;

final class GetDefaultNotificationRulesActionTest extends TestCase
{
    public function test_it_builds_default_notification_rules(): void
    {
        $rules = (new GetDefaultNotificationRulesAction)->execute();

        $this->assertCount(5, $rules);
        $this->assertSame(NotificationEvent::TaskDeadlineReminder, $rules[0]->event);
        $this->assertContains(NotificationChannel::Email, $rules[0]->channels);
        $this->assertContains(NotificationChannel::WhatsApp, $rules[0]->channels);
    }

    public function test_finance_approval_rule_targets_treasurer_in_app(): void
    {
        $rule = collect((new GetDefaultNotificationRulesAction)->execute())
            ->firstWhere('event', NotificationEvent::FinanceApprovalRequested);

        $this->assertNotNull($rule);
        $this->assertSame('Treasurer', $rule->audience);
        $this->assertSame([NotificationChannel::InApp], $rule->channels);
    }

    public function test_rule_serializes_for_inertia_payloads(): void
    {
        $payload = (new GetDefaultNotificationRulesAction)->execute()[0]->toArray();

        $this->assertSame('task_deadline_reminder', $payload['event']);
        $this->assertSame('Task deadline reminder', $payload['label']);
        $this->assertSame(['in_app', 'email', 'whatsapp'], $payload['channels']);
        $this->assertSame('planned', $payload['status']);
    }
}
