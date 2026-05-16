<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lab404\Impersonate\Services\ImpersonateManager;
use Livewire\Livewire;
use Tests\TestCase;

final class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_can_impersonate_a_regular_user(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);

        Livewire::test(ListUsers::class)
            ->callTableAction('impersonate', $target);

        $this->assertSame($target->id, auth()->id());

        $log = ActivityLog::query()
            ->where('action', 'impersonate.start')
            ->where('target_id', $target->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($target->id, $log->payload['target_user_id'] ?? null);
        $this->assertSame($target->email, $log->payload['target_user_email'] ?? null);
        $this->assertSame($superAdmin->id, $log->user_id);
    }

    public function test_get_impersonation_take_route_logs_start_and_redirects_to_dashboard(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $response = $this->actingAs($superAdmin)->get(route('impersonate', ['id' => $target->id]));

        $response->assertRedirect(route('dashboard'));

        $this->assertSame($target->id, auth()->id());

        $log = ActivityLog::query()
            ->where('action', 'impersonate.start')
            ->where('target_id', $target->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($superAdmin->id, $log->user_id);
        $this->assertSame($target->id, $log->payload['target_user_id'] ?? null);
    }

    public function test_super_admin_cannot_impersonate_another_super_admin(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $secondSuperAdmin = User::factory()->create(['email' => 'second-super@prokerin.test']);
        $secondSuperAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin);

        $this->assertFalse($secondSuperAdmin->canBeImpersonated());
    }

    public function test_organization_owner_cannot_trigger_impersonate_action(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->assertFalse($owner->canImpersonate());
    }

    public function test_stop_impersonation_returns_to_internal_admin_and_logs_activity(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);

        $manager = app(ImpersonateManager::class);
        $superAdmin->impersonate($target);
        session()->put('impersonate_started_at', now()->toIso8601String());

        $this->assertTrue($manager->isImpersonating());
        $this->assertSame($target->id, auth()->id());

        $response = $this->post(route('impersonate.stop'));

        $response->assertRedirect('/internal-admin/users');

        $log = ActivityLog::query()
            ->where('action', 'impersonate.stop')
            ->where('target_id', $target->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($target->id, $log->payload['target_user_id'] ?? null);
        $this->assertSame($superAdmin->id, $log->user_id);
    }

    public function test_get_impersonation_leave_route_logs_stop_as_super_admin_actor(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);
        $superAdmin->impersonate($target);
        session()->put('impersonate_started_at', now()->toIso8601String());

        $response = $this->get(route('impersonate.leave'));

        $response->assertRedirect('/internal-admin/users');

        $log = ActivityLog::query()
            ->where('action', 'impersonate.stop')
            ->where('target_id', $target->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame($target->id, $log->payload['target_user_id'] ?? null);
        $this->assertSame($superAdmin->id, $log->user_id);
    }
}
