<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ProposalApprovalDecidedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\WebPush\WebPushChannel;
use Tests\TestCase;

final class WebPushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_authenticated_user_can_store_web_push_subscription(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('webpush.subscribe'), $this->payload())
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Web push subscription saved.',
            ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_type' => User::class,
            'subscribable_id' => $user->id,
            'endpoint' => 'https://push.example.test/subscription/owner',
            'public_key' => 'demo-public-key',
            'auth_token' => 'demo-auth-token',
            'content_encoding' => 'aesgcm',
        ]);
    }

    public function test_authenticated_user_can_delete_web_push_subscription(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $user->updatePushSubscription(
            'https://push.example.test/subscription/owner',
            'demo-public-key',
            'demo-auth-token',
            'aesgcm',
        );

        $this->actingAs($user)
            ->deleteJson(route('webpush.unsubscribe'), [
                'endpoint' => 'https://push.example.test/subscription/owner',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Web push subscription removed.',
            ]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://push.example.test/subscription/owner',
        ]);
    }

    public function test_proposal_decision_notification_adds_web_push_channel_when_vapid_is_configured(): void
    {
        config()->set('webpush.vapid.public_key', 'configured-public-key');
        config()->set('webpush.vapid.private_key', 'configured-private-key');

        $notification = new ProposalApprovalDecidedNotification(
            projectName: 'Surabaya Career Week 2026',
            decision: 'approved',
            approverName: 'Aurelia Putri',
            resourceUrl: '/reports/proposal-editor',
        );

        $channels = $notification->via(User::query()->where('email', 'owner@prokerin.test')->firstOrFail());
        $message = $notification->toWebPush(new \stdClass)->toArray();

        $this->assertContains(WebPushChannel::class, $channels);
        $this->assertSame('Proposal disetujui', $message['title']);
        $this->assertSame('/reports/proposal-editor', $message['data']['url']);
    }

    /**
     * @return array{endpoint: string, keys: array{p256dh: string, auth: string}}
     */
    private function payload(): array
    {
        return [
            'endpoint' => 'https://push.example.test/subscription/owner',
            'keys' => [
                'p256dh' => 'demo-public-key',
                'auth' => 'demo-auth-token',
            ],
        ];
    }
}
