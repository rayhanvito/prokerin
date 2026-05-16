<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class CampusDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_campus_admin_can_view_linked_organization_aggregate_dashboard(): void
    {
        $campusAdmin = User::query()->where('email', 'campus@prokerin.test')->firstOrFail();

        $response = $this->actingAs($campusAdmin)->get(route('campus.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Campus/Dashboard')
            ->where('campus.name', 'Universitas Nusantara')
            ->where('metrics.0.value', 2)
            ->has('organizations', 2)
            ->where('organizations.0.name', 'BEM Fakultas Teknologi')
            ->where('organizations.1.name', 'HIMA Informatika')
            ->has('recentProjects'));
    }

    public function test_campus_dashboard_does_not_leak_unlinked_organizations(): void
    {
        $campusAdmin = User::query()->where('email', 'campus@prokerin.test')->firstOrFail();

        $response = $this->actingAs($campusAdmin)->get(route('campus.dashboard'));

        $response->assertOk();
        $response->assertDontSee('UKM Kreatif');
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Campus/Dashboard')
            ->where('metrics.0.value', 2)
            ->has('organizations', 2));
    }

    public function test_non_campus_admin_cannot_view_campus_dashboard(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('campus.dashboard'))
            ->assertForbidden();
    }

    public function test_campus_admin_cannot_mutate_organization_data(): void
    {
        $campusAdmin = User::query()->where('email', 'campus@prokerin.test')->firstOrFail();

        $this->actingAs($campusAdmin)
            ->post(route('proker.store'), [
                'name' => 'Program Kampus Tidak Boleh Dibuat',
                'description' => 'Campus dashboard is read-only.',
                'template_type' => 'seminar',
                'starts_at' => '2026-08-01',
                'ends_at' => '2026-08-02',
                'project_lead_id' => null,
            ])
            ->assertForbidden();
    }
}
