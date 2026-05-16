<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\EventRegistrationConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_public_event_registration_page_is_accessible_without_authentication(): void
    {
        $this->get(route('events.register.show', ['project' => 'seminar-karier-digital']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Events/Register')
                ->where('event.name', 'Seminar Karier Digital')
                ->where('settings.isOpen', true)
                ->where('settings.registeredCount', 2));
    }

    public function test_public_participant_can_register_for_open_event(): void
    {
        Notification::fake();

        $this->post(route('events.register.store', ['project' => 'seminar-karier-digital']), [
            'participant_name' => 'Citra Maharani',
            'participant_email' => 'citra.maharani@student.example',
            'phone' => '+6282112340999',
            'institution' => 'Universitas Airlangga',
        ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Registrasi berhasil dikirim. Silakan cek email konfirmasi Anda.');

        $this->assertDatabaseHas('event_registrations', [
            'project_id' => $this->projectId('seminar-karier-digital'),
            'participant_email' => 'citra.maharani@student.example',
            'status' => 'confirmed',
        ]);

        Notification::assertSentOnDemand(EventRegistrationConfirmedNotification::class);
    }

    public function test_duplicate_email_is_rejected_per_event(): void
    {
        $this->post(route('events.register.store', ['project' => 'seminar-karier-digital']), [
            'participant_name' => 'Alya Rahma',
            'participant_email' => 'alya.rahma@student.example',
            'phone' => '+6282112340001',
            'institution' => 'Universitas Negeri Surabaya',
        ])
            ->assertSessionHasErrors('participant_email');
    }

    public function test_capacity_is_enforced_for_public_registration(): void
    {
        DB::table('event_registration_settings')
            ->where('project_id', $this->projectId('seminar-karier-digital'))
            ->update(['capacity' => 2]);

        $this->post(route('events.register.store', ['project' => 'seminar-karier-digital']), [
            'participant_name' => 'Dewi Kartika',
            'participant_email' => 'dewi.kartika@student.example',
            'phone' => '+6282112340888',
            'institution' => 'Universitas Brawijaya',
        ])
            ->assertSessionHasErrors('participant_email');
    }

    public function test_registration_window_is_enforced(): void
    {
        DB::table('event_registration_settings')
            ->where('project_id', $this->projectId('seminar-karier-digital'))
            ->update([
                'opens_at' => now()->addDay(),
                'closes_at' => now()->addDays(10),
            ]);

        $this->post(route('events.register.store', ['project' => 'seminar-karier-digital']), [
            'participant_name' => 'Eka Prasetyo',
            'participant_email' => 'eka.prasetyo@student.example',
            'phone' => '+6282112340777',
            'institution' => 'Universitas Negeri Malang',
        ])
            ->assertSessionHasErrors('participant_email');
    }

    public function test_internal_registration_list_is_tenant_scoped(): void
    {
        $outsider = User::factory()->create();
        $himaOrganizationId = $this->organizationId('hima-informatika');
        $himaProjectId = $this->projectId('workshop-ui-ux-hmif');

        DB::table('organization_members')->insert([
            'organization_id' => $himaOrganizationId,
            'user_id' => $outsider->id,
            'role' => 'viewer',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('event_registration_settings')->insert([
            'project_id' => $himaProjectId,
            'is_open' => true,
            'capacity' => 40,
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addMonth(),
            'require_payment' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('event_registrations')->insert([
            'project_id' => $himaProjectId,
            'participant_name' => 'Peserta HMIF',
            'participant_email' => 'peserta.hmif@student.example',
            'phone' => '+6282112340666',
            'institution' => 'HIMA Informatika',
            'status' => 'confirmed',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($outsider)
            ->get(route('events.registrations.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Events/Registrations')
                ->has('events', 1)
                ->where('events.0.name', 'Workshop UI/UX HMIF')
                ->has('registrations', 1)
                ->where('registrations.0.participantName', 'Peserta HMIF'));
    }

    public function test_internal_registration_export_is_tenant_scoped_csv(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($owner)
            ->get(route('events.registrations.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Seminar Karier Digital', $content);
        $this->assertStringContainsString('Alya Rahma', $content);
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
