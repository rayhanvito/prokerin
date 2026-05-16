<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Dashboard\GetDashboardOverviewAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $payload = $this->app->make(GetDashboardOverviewAction::class)->execute();

        $this->assertArrayHasKey('metrics', $payload);
        $this->assertArrayHasKey('priorityProjects', $payload);
        $this->assertArrayHasKey('weeklyFocus', $payload);
        $this->assertArrayHasKey('memberSummary', $payload);
        $this->assertSame('3', $payload['metrics'][0]['value']);
        $this->assertSame('8 anggota aktif', $payload['memberSummary']['value']);
    }
}
