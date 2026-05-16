<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardRedirectsForPlatformAdminsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_is_redirected_from_dashboard_to_internal_admin(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $response = $this->actingAs($superAdmin)->get(route('dashboard'));

        $response->assertRedirect('/internal-admin');
    }

    public function test_campus_admin_is_redirected_from_dashboard_to_campus_dashboard(): void
    {
        $campusAdmin = User::query()->where('email', 'campus@prokerin.test')->firstOrFail();

        $response = $this->actingAs($campusAdmin)->get(route('dashboard'));

        $response->assertRedirect(route('campus.dashboard'));
    }

    public function test_user_with_no_membership_still_receives_403(): void
    {
        $orphan = User::factory()->create([
            'email' => 'orphan@prokerin.test',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($orphan)->get(route('dashboard'));

        $response->assertForbidden();
    }
}
