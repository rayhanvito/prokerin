<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Membership\InvitationStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class OrganizationInvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_send_invitation_and_member_cannot(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('organization.invitations.store'), [
                'email' => 'new.member@kampus.test',
                'role' => 'member',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Invitation berhasil dikirim.');

        $this->assertDatabaseHas('organization_invitations', [
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'email' => 'new.member@kampus.test',
            'role' => 'member',
            'status' => InvitationStatus::Pending->value,
        ]);

        $this->actingAs($member)
            ->post(route('organization.invitations.store'), [
                'email' => 'blocked@kampus.test',
                'role' => 'viewer',
            ])
            ->assertForbidden();
    }

    public function test_duplicate_pending_invitation_and_existing_member_are_rejected(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('organization.invitations.store'), [
                'email' => 'calon.sekretaris@kampus.test',
                'role' => 'secretary',
            ])
            ->assertSessionHas('error', 'Invitation aktif untuk email ini sudah ada.');

        $this->actingAs($owner)
            ->post(route('organization.invitations.store'), [
                'email' => 'member@prokerin.test',
                'role' => 'member',
            ])
            ->assertSessionHas('error', 'Email ini sudah menjadi anggota organisasi.');
    }

    public function test_invited_user_can_accept_invitation_and_other_user_cannot(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $invitee = User::factory()->create(['email' => 'accepted@kampus.test']);
        $other = User::factory()->create(['email' => 'other@kampus.test']);

        $this->actingAs($owner)
            ->post(route('organization.invitations.store'), [
                'email' => 'accepted@kampus.test',
                'role' => 'viewer',
            ])
            ->assertRedirect();

        $token = (string) DB::table('organization_invitations')
            ->where('email', 'accepted@kampus.test')
            ->value('token');

        $this->actingAs($other)
            ->post(route('invitations.accept', ['token' => $token]))
            ->assertSessionHas('error', 'Invitation ini hanya bisa dipakai oleh email tujuan.');

        $this->actingAs($invitee)
            ->post(route('invitations.accept', ['token' => $token]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Invitation berhasil diterima.');

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'user_id' => $invitee->id,
            'role' => 'viewer',
        ]);
        $this->assertDatabaseHas('organization_invitations', [
            'email' => 'accepted@kampus.test',
            'status' => InvitationStatus::Accepted->value,
            'accepted_by_user_id' => $invitee->id,
        ]);
    }

    public function test_invited_user_can_decline_and_cannot_accept_afterward(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $invitee = User::factory()->create(['email' => 'declined@kampus.test']);

        $this->actingAs($owner)
            ->post(route('organization.invitations.store'), [
                'email' => 'declined@kampus.test',
                'role' => 'member',
            ])
            ->assertRedirect();

        $token = (string) DB::table('organization_invitations')
            ->where('email', 'declined@kampus.test')
            ->value('token');

        $this->actingAs($invitee)
            ->post(route('invitations.decline', ['token' => $token]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', 'Invitation sudah ditolak.');

        $this->actingAs($invitee)
            ->post(route('invitations.accept', ['token' => $token]))
            ->assertSessionHas('error', 'Invitation ini sudah tidak aktif.');
    }

    public function test_expired_invitation_is_rejected(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $invitee = User::factory()->create(['email' => 'expired-user@kampus.test']);

        $this->actingAs($owner)
            ->post(route('organization.invitations.store'), [
                'email' => 'expired-user@kampus.test',
                'role' => 'member',
            ])
            ->assertRedirect();

        $token = (string) DB::table('organization_invitations')
            ->where('email', 'expired-user@kampus.test')
            ->value('token');

        DB::table('organization_invitations')
            ->where('token', $token)
            ->update(['expires_at' => now()->subDay()]);

        $this->actingAs($invitee)
            ->post(route('invitations.accept', ['token' => $token]))
            ->assertSessionHas('error', 'Invitation ini sudah kedaluwarsa.');

        $this->assertDatabaseHas('organization_invitations', [
            'email' => 'expired-user@kampus.test',
            'status' => InvitationStatus::Expired->value,
        ]);
    }

    public function test_invitation_preview_renders_payload(): void
    {
        $token = (string) DB::table('organization_invitations')
            ->where('email', 'calon.sekretaris@kampus.test')
            ->value('token');

        $this->get(route('invitations.show', ['token' => $token]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Invitations/Show')
                ->where('invitation.email', 'calon.sekretaris@kampus.test')
                ->where('invitation.organizationName', 'BEM Fakultas Teknologi')
                ->where('invitation.isOpen', true));
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
