<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Dashboard\GetDashboardOverviewAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetDashboardOverviewActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_returns_dashboard_payload_sections(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $payload = $this->app->make(GetDashboardOverviewAction::class)->execute((int) $user->id);

        $this->assertArrayHasKey('metrics', $payload);
        $this->assertArrayHasKey('priorityProjects', $payload);
        $this->assertArrayHasKey('weeklyFocus', $payload);
        $this->assertArrayHasKey('memberSummary', $payload);
        $this->assertSame('1', $payload['metrics'][0]['value']);
        $this->assertSame('11 anggota aktif', $payload['memberSummary']['value']);
    }

    public function test_it_scopes_dashboard_payload_to_actor_organizations(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $now = now();
        $externalOrganizationId = (int) DB::table('organizations')->insertGetId([
            'name' => 'External HIMA',
            'slug' => 'external-hima',
            'logo_path' => null,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('projects')->insert([
            'organization_id' => $externalOrganizationId,
            'organization_period_id' => null,
            'project_template_id' => null,
            'project_lead_id' => null,
            'name' => 'External Project',
            'slug' => 'external-project',
            'description' => 'Should not leak into owner dashboard.',
            'status' => 'draft',
            'progress' => 90,
            'starts_at' => '2026-08-01',
            'ends_at' => '2026-08-02',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $payload = $this->app->make(GetDashboardOverviewAction::class)->execute((int) $user->id);

        $this->assertSame('1', $payload['metrics'][0]['value']);
        $this->assertNotContains('External Project', array_column($payload['priorityProjects'], 'title'));
    }
}
