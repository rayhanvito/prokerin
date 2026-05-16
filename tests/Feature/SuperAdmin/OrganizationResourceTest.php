<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OrganizationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_can_change_organization_plan_tier_and_log_activity(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();
        $organization = Organization::query()->where('slug', 'hima-informatika')->firstOrFail();

        $this->assertSame('free', (string) $organization->getRawOriginal('plan_tier'));

        $this->actingAs($superAdmin);

        Livewire::test(EditOrganization::class, ['record' => $organization->getRouteKey()])
            ->fillForm([
                'name' => $organization->name,
                'slug' => $organization->slug,
                'status' => $organization->status,
                'plan_tier' => 'pro',
                'internal_notes' => 'Bumped to pro for partnership demo',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $organization->refresh();

        $this->assertSame('pro', (string) $organization->getRawOriginal('plan_tier'));
        $this->assertSame('Bumped to pro for partnership demo', (string) $organization->internal_notes);

        $log = ActivityLog::query()
            ->where('action', 'org.plan_tier.change')
            ->where('target_type', Organization::class)
            ->where('target_id', $organization->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('free', $log->payload['before'] ?? null);
        $this->assertSame('pro', $log->payload['after'] ?? null);
        $this->assertSame($superAdmin->id, $log->user_id);
    }
}
