<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class FinanceOverviewPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_finance_overview_receives_database_backed_metrics(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $this->actingAs($treasurer)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Finance/Index')
                ->where('metrics.0.value', 8250000)
                ->where('metrics.1.value', 2650000)
                ->where('metrics.2.value', 5600000)
                ->where('metrics.3.value', 1)
                ->where('monthlyRealization.0.amount', 2000000)
                ->where('topCategories.0.category', 'Venue')
                ->where('reviewLines.0.name', 'Sewa aula dan sound system'));
    }

    public function test_finance_overview_respects_tenant_scope(): void
    {
        $otherOwner = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($otherOwner)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Finance/Index')
                ->where('metrics.0.value', 0)
                ->where('metrics.1.value', 0)
                ->where('metrics.2.value', 0)
                ->where('metrics.3.value', 0)
                ->has('monthlyRealization', 0)
                ->has('reviewLines', 0));
    }
}
