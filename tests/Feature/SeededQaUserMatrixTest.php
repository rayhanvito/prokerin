<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SeededQaUserMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_qa_master_seeded_user_matrix_exists(): void
    {
        $expectedUsers = [
            'owner@prokerin.test',
            'admin@prokerin.test',
            'secretary@prokerin.test',
            'treasurer@prokerin.test',
            'lead@prokerin.test',
            'coordinator@prokerin.test',
            'member@prokerin.test',
            'viewer@prokerin.test',
            'owner2@prokerin.test',
            'superadmin@prokerin.internal',
        ];

        foreach ($expectedUsers as $email) {
            $this->assertDatabaseHas('users', ['email' => $email]);
        }
    }

    public function test_qa_master_seeded_organization_roles_are_available(): void
    {
        $bemOrganizationId = (int) DB::table('organizations')
            ->where('slug', 'bem-fakultas-teknologi')
            ->value('id');

        $ukmOrganizationId = (int) DB::table('organizations')
            ->where('slug', 'ukm-kreatif')
            ->value('id');

        $expectedRoles = [
            'owner@prokerin.test' => 'organization_owner',
            'admin@prokerin.test' => 'organization_admin',
            'secretary@prokerin.test' => 'secretary',
            'treasurer@prokerin.test' => 'treasurer',
            'lead@prokerin.test' => 'project_lead',
            'coordinator@prokerin.test' => 'division_coordinator',
            'member@prokerin.test' => 'member',
            'viewer@prokerin.test' => 'viewer',
        ];

        foreach ($expectedRoles as $email => $role) {
            $this->assertDatabaseHas('organization_members', [
                'organization_id' => $bemOrganizationId,
                'user_id' => User::query()->where('email', $email)->value('id'),
                'role' => $role,
            ]);
        }

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $ukmOrganizationId,
            'user_id' => User::query()->where('email', 'owner2@prokerin.test')->value('id'),
            'role' => 'organization_owner',
        ]);
    }

    public function test_super_admin_internal_account_has_spatie_role(): void
    {
        $superAdmin = User::query()
            ->where('email', 'superadmin@prokerin.internal')
            ->firstOrFail();

        $this->assertTrue($superAdmin->hasRole('super_admin'));
    }
}
