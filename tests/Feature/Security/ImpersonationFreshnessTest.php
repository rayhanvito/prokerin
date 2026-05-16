<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lab404\Impersonate\Services\ImpersonateManager;
use Tests\TestCase;

final class ImpersonationFreshnessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_active_impersonation_within_max_duration_is_kept(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);
        app(ImpersonateManager::class)->take($superAdmin, $target);
        session()->put('impersonate_started_at', now()->toIso8601String());

        $response = $this->get(route('dashboard'));

        $this->assertNotEquals('/internal-admin/users', $response->headers->get('Location'));
        $this->assertTrue(app(ImpersonateManager::class)->isImpersonating());
    }

    public function test_impersonation_expires_after_configured_duration(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);
        app(ImpersonateManager::class)->take($superAdmin, $target);

        $hours = (int) config('prokerin.impersonate.max_duration_hours', 2);
        session()->put(
            'impersonate_started_at',
            now()->subHours($hours + 1)->toIso8601String(),
        );

        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/internal-admin/users');
        $this->assertFalse(app(ImpersonateManager::class)->isImpersonating());
    }

    public function test_missing_started_at_marker_forces_expiry(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.test')->firstOrFail();
        $target = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($superAdmin);
        app(ImpersonateManager::class)->take($superAdmin, $target);
        // intentionally NOT setting impersonate_started_at

        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/internal-admin/users');
        $this->assertFalse(app(ImpersonateManager::class)->isImpersonating());
    }
}
