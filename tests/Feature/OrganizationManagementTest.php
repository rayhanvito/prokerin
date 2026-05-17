<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_switcher_uses_membership_payload_and_can_switch_active_organization(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $himaId = $this->organizationId('hima-informatika');

        $this->actingAs($admin)
            ->get(route('organization.switcher'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/Switcher')
                ->has('organizations', 2)
                ->where('organizations.0.name', 'BEM Fakultas Teknologi')
                ->where('organizations.1.name', 'HIMA Informatika'));

        $this->actingAs($admin)
            ->post(route('organization.switch'), [
                'organization_id' => $himaId,
            ])
            ->assertRedirect()
            ->assertSessionHas('active_organization_id', $himaId);

        $this->actingAs($admin)
            ->withSession(['active_organization_id' => $himaId])
            ->get(route('organization.switcher'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('activeOrganizationId', $himaId)
                ->where('organizations.1.active', true));
    }

    public function test_user_cannot_switch_to_non_member_organization(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('organization.switch'), [
                'organization_id' => $this->organizationId('ukm-kreatif'),
            ])
            ->assertForbidden();
    }

    public function test_owner_can_create_active_period_for_active_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('bem-fakultas-teknologi');

        $this->actingAs($owner)
            ->withSession(['active_organization_id' => $organizationId])
            ->post(route('organization.periods.store'), [
                'name' => '2027',
                'starts_at' => '2027-01-01',
                'ends_at' => '2027-12-31',
                'is_active' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Periode kepengurusan berhasil ditambahkan.');

        $this->assertDatabaseHas('organization_periods', [
            'organization_id' => $organizationId,
            'name' => '2027',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('organization_periods', [
            'organization_id' => $organizationId,
            'name' => '2026',
            'is_active' => false,
        ]);
    }

    public function test_owner_can_set_existing_period_active(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('bem-fakultas-teknologi');
        $periodId = (int) DB::table('organization_periods')->insertGetId([
            'organization_id' => $organizationId,
            'name' => '2027',
            'starts_at' => '2027-01-01',
            'ends_at' => '2027-12-31',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)
            ->patch(route('organization.periods.update', ['period' => $periodId]), [
                'name' => '2027',
                'starts_at' => '2027-01-01',
                'ends_at' => '2027-12-31',
                'is_active' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Periode kepengurusan berhasil diperbarui.');

        $this->assertDatabaseHas('organization_periods', [
            'id' => $periodId,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('organization_periods', [
            'organization_id' => $organizationId,
            'name' => '2026',
            'is_active' => false,
        ]);
    }

    public function test_owner_cannot_update_other_tenant_period(): void
    {
        $owner2 = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();
        $periodId = (int) DB::table('organization_periods')
            ->where('organization_id', $this->organizationId('bem-fakultas-teknologi'))
            ->value('id');

        $this->actingAs($owner2)
            ->patch(route('organization.periods.update', ['period' => $periodId]), [
                'name' => '2027',
                'starts_at' => '2027-01-01',
                'ends_at' => '2027-12-31',
                'is_active' => true,
            ])
            ->assertForbidden();
    }

    public function test_member_cannot_create_period_or_update_organization(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('organization.periods.store'), [
                'name' => '2027',
                'starts_at' => '2027-01-01',
                'ends_at' => '2027-12-31',
                'is_active' => true,
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('organization.update'), [
                'name' => 'Nama Tidak Boleh',
            ])
            ->assertForbidden();
    }

    public function test_owner_can_update_active_organization_profile(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('bem-fakultas-teknologi');

        $this->actingAs($owner)
            ->withSession(['active_organization_id' => $organizationId])
            ->patch(route('organization.update'), [
                'name' => 'BEM FT Nusantara',
                'description' => 'Organisasi eksekutif mahasiswa Fakultas Teknologi.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Profil organisasi berhasil diperbarui.');

        $this->assertDatabaseHas('organizations', [
            'id' => $organizationId,
            'name' => 'BEM FT Nusantara',
            'description' => 'Organisasi eksekutif mahasiswa Fakultas Teknologi.',
        ]);
    }

    public function test_admin_cannot_update_organization_profile(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('organization.update'), [
                'name' => 'Nama Admin Ditolak',
            ])
            ->assertForbidden();
    }

    public function test_periods_and_calendar_pages_are_database_backed(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('organization.periods'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/Periods')
                ->where('organization.name', 'BEM Fakultas Teknologi')
                ->where('periods.0.period', '2026'));

        $this->actingAs($owner)
            ->get(route('organization.calendar', ['month' => '2026-06']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/Calendar')
                ->where('month', '2026-06')
                ->where('metrics.0.label', 'Agenda')
                ->has('events', 1)
                ->where('events.0.type', 'project')
                ->where('events.0.title', 'Seminar Karier Digital')
                ->where('events.0.link', '/proker/seminar-karier-digital'));
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
