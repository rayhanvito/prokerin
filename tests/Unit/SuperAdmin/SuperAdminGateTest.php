<?php

declare(strict_types=1);

namespace Tests\Unit\SuperAdmin;

use App\Filament\SuperAdminGate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SuperAdminGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'organization_owner', 'guard_name' => 'web']);
    }

    public function test_it_grants_access_to_user_with_super_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        $this->assertTrue(SuperAdminGate::canAccess());
    }

    public function test_it_denies_access_to_user_with_organization_owner_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('organization_owner');

        $this->actingAs($user);

        $this->assertFalse(SuperAdminGate::canAccess());
    }

    public function test_it_denies_access_to_user_with_no_roles(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertFalse(SuperAdminGate::canAccess());
    }

    public function test_it_denies_access_to_unauthenticated_request(): void
    {
        $this->assertFalse(SuperAdminGate::canAccess());
    }
}
