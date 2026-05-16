<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FilamentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_guest_is_redirected_away_from_internal_admin(): void
    {
        $response = $this->get('/internal-admin');

        $response->assertRedirect();
    }

    public function test_organization_owner_is_blocked_from_internal_admin(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($owner)->get('/internal-admin');

        // Filament returns 403 when canAccessPanel returns false
        $this->assertContains($response->getStatusCode(), [403, 302]);
    }

    public function test_member_is_blocked_from_internal_admin(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $response = $this->actingAs($member)->get('/internal-admin');

        $this->assertContains($response->getStatusCode(), [403, 302]);
    }

    public function test_super_admin_can_access_internal_admin_dashboard(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $response = $this->actingAs($superAdmin)->get('/internal-admin');

        $response->assertOk();
    }

    public function test_super_admin_can_list_users(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/internal-admin/users')
            ->assertOk();
    }

    public function test_super_admin_can_list_organizations(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/internal-admin/organizations')
            ->assertOk();
    }

    public function test_super_admin_can_list_projects(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/internal-admin/projects')
            ->assertOk();
    }

    public function test_super_admin_can_list_notification_rules(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/internal-admin/notification-rules')
            ->assertOk();
    }

    public function test_organization_owner_cannot_open_users_resource_route(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($owner)->get('/internal-admin/users');

        $this->assertContains($response->getStatusCode(), [403, 302, 404]);
    }
}
