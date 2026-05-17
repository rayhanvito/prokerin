<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class ProkerIndexPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_proker_index_is_database_backed_for_active_tenant(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('proker.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Index')
                ->where('metrics.0.value', '1')
                ->has('projects', 1)
                ->where('projects.0.slug', 'seminar-karier-digital')
                ->where('projects.0.name', 'Seminar Karier Digital'));
    }

    public function test_proker_index_does_not_leak_other_tenant_projects(): void
    {
        $owner2 = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($owner2)
            ->get(route('proker.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Index')
                ->has('projects', 1)
                ->where('projects.0.slug', 'makrab-angkatan-2026'));
    }

    public function test_proker_index_filters_by_status_and_search(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['active_organization_id' => $this->organizationId('hima-informatika')])
            ->get(route('proker.index', [
                'status' => 'rab_approval',
                'search' => 'Workshop',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Index')
                ->where('filters.status', 'rab_approval')
                ->where('filters.search', 'Workshop')
                ->has('projects', 1)
                ->where('projects.0.slug', 'workshop-ui-ux-hmif'));
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')
            ->where('slug', $slug)
            ->value('id');
    }
}
