<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PaymentTicketingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Config::set('services.midtrans.server_key', 'midtrans-server-test-key');
    }

    public function test_public_registration_page_receives_active_ticket_tiers(): void
    {
        $this->get(route('events.register.show', ['project' => 'seminar-karier-digital']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Events/Register')
                ->has('ticketTiers', 3)
                ->where('ticketTiers.0.name', 'Free Pass')
                ->where('ticketTiers.1.price', 25000));
    }

    public function test_free_ticket_tier_confirms_registration_without_payment_order(): void
    {
        $freeTierId = $this->ticketTierId('Free Pass');

        $this->post(route('events.register.store', ['project' => 'seminar-karier-digital']), [
            'participant_name' => 'Gratis Peserta',
            'participant_email' => 'gratis.peserta@student.example',
            'phone' => '+6282112340101',
            'institution' => 'Universitas Negeri Surabaya',
            'ticket_tier_id' => $freeTierId,
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $registrationId = (int) DB::table('event_registrations')
            ->where('participant_email', 'gratis.peserta@student.example')
            ->value('id');

        $this->assertGreaterThan(0, $registrationId);
        $this->assertDatabaseHas('event_registrations', [
            'id' => $registrationId,
            'ticket_tier_id' => $freeTierId,
            'status' => 'confirmed',
        ]);
        $this->assertDatabaseMissing('payment_orders', [
            'registration_id' => $registrationId,
        ]);
    }

    public function test_paid_ticket_tier_creates_pending_payment_order(): void
    {
        $paidTierId = $this->ticketTierId('Early Bird');

        $this->post(route('events.register.store', ['project' => 'seminar-karier-digital']), [
            'participant_name' => 'Bayar Peserta',
            'participant_email' => 'bayar.peserta@student.example',
            'phone' => '+6282112340202',
            'institution' => 'Institut Teknologi Sepuluh Nopember',
            'ticket_tier_id' => $paidTierId,
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $registrationId = (int) DB::table('event_registrations')
            ->where('participant_email', 'bayar.peserta@student.example')
            ->value('id');

        $this->assertDatabaseHas('event_registrations', [
            'id' => $registrationId,
            'ticket_tier_id' => $paidTierId,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_orders', [
            'registration_id' => $registrationId,
            'tier_id' => $paidTierId,
            'amount' => 25000,
            'status' => 'pending',
        ]);
    }

    public function test_ticket_tier_capacity_is_enforced(): void
    {
        $tierId = $this->ticketTierId('Early Bird');

        DB::table('ticket_tiers')
            ->where('id', $tierId)
            ->update(['capacity' => 1]);

        DB::table('event_registrations')->insert([
            'project_id' => $this->projectId('seminar-karier-digital'),
            'ticket_tier_id' => $tierId,
            'participant_name' => 'Existing Paid',
            'participant_email' => 'existing.paid@student.example',
            'phone' => '+6282112340303',
            'institution' => 'Universitas Airlangga',
            'status' => 'pending',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('events.register.store', ['project' => 'seminar-karier-digital']), [
            'participant_name' => 'Blocked Paid',
            'participant_email' => 'blocked.paid@student.example',
            'phone' => '+6282112340404',
            'institution' => 'Universitas Brawijaya',
            'ticket_tier_id' => $tierId,
        ])
            ->assertSessionHasErrors('ticket_tier_id');
    }

    public function test_midtrans_webhook_valid_signature_marks_order_paid_and_confirms_registration(): void
    {
        $order = $this->createPendingPaymentOrder();
        $payload = $this->midtransPayload(
            orderId: (string) $order->provider_order_id,
            grossAmount: '25000.00',
            transactionStatus: 'settlement',
        );

        $this->postJson(route('payments.midtrans.webhook'), $payload)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('payment_orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('event_registrations', [
            'id' => $order->registration_id,
            'status' => 'confirmed',
        ]);
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        $order = $this->createPendingPaymentOrder();
        $payload = $this->midtransPayload(
            orderId: (string) $order->provider_order_id,
            grossAmount: '25000.00',
            transactionStatus: 'settlement',
        );
        $payload['signature_key'] = 'invalid-signature';

        $this->postJson(route('payments.midtrans.webhook'), $payload)
            ->assertUnprocessable();

        $this->assertDatabaseHas('payment_orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_midtrans_expire_webhook_cancels_pending_registration(): void
    {
        $order = $this->createPendingPaymentOrder();
        $payload = $this->midtransPayload(
            orderId: (string) $order->provider_order_id,
            grossAmount: '25000.00',
            transactionStatus: 'expire',
        );

        $this->postJson(route('payments.midtrans.webhook'), $payload)
            ->assertOk()
            ->assertJsonPath('data.status', 'expired');

        $this->assertDatabaseHas('payment_orders', [
            'id' => $order->id,
            'status' => 'expired',
        ]);
        $this->assertDatabaseHas('event_registrations', [
            'id' => $order->registration_id,
            'status' => 'cancelled',
        ]);
    }

    private function createPendingPaymentOrder(): object
    {
        $tierId = $this->ticketTierId('Early Bird');
        $registrationId = (int) DB::table('event_registrations')->insertGetId([
            'project_id' => $this->projectId('seminar-karier-digital'),
            'ticket_tier_id' => $tierId,
            'participant_name' => 'Webhook Peserta',
            'participant_email' => 'webhook.peserta@student.example',
            'phone' => '+6282112340505',
            'institution' => 'Universitas Negeri Malang',
            'status' => 'pending',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $orderId = (int) DB::table('payment_orders')->insertGetId([
            'registration_id' => $registrationId,
            'tier_id' => $tierId,
            'amount' => 25000,
            'status' => 'pending',
            'provider_order_id' => 'PRK-MIDTRANS-TEST-'.$registrationId,
            'paid_at' => null,
            'expires_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('payment_orders')->where('id', $orderId)->first();
    }

    /**
     * @return array{order_id: string, status_code: string, gross_amount: string, signature_key: string, transaction_status: string, fraud_status: string}
     */
    private function midtransPayload(string $orderId, string $grossAmount, string $transactionStatus): array
    {
        $statusCode = '200';

        return [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $orderId.$statusCode.$grossAmount.'midtrans-server-test-key'),
            'transaction_status' => $transactionStatus,
            'fraud_status' => 'accept',
        ];
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }

    private function ticketTierId(string $name): int
    {
        return (int) DB::table('ticket_tiers')->where('name', $name)->value('id');
    }
}
