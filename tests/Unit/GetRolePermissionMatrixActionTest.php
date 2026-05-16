<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Membership\GetRolePermissionMatrixAction;
use App\Domain\Membership\PermissionKey;
use PHPUnit\Framework\TestCase;

final class GetRolePermissionMatrixActionTest extends TestCase
{
    public function test_it_builds_role_permission_matrix(): void
    {
        $matrix = (new GetRolePermissionMatrixAction)->execute();

        $this->assertCount(10, $matrix);
        $this->assertSame('organization_owner', $matrix[0]->role);
        $this->assertTrue($matrix[0]->isSystemRole);
        $this->assertTrue($matrix[0]->hasPermission(PermissionKey::ManageOrganization));
    }

    public function test_treasurer_can_approve_budget_without_managing_members(): void
    {
        $treasurer = collect((new GetRolePermissionMatrixAction)->execute())
            ->firstWhere('role', 'treasurer');

        $this->assertNotNull($treasurer);
        $this->assertTrue($treasurer->hasPermission(PermissionKey::ApproveBudget));
        $this->assertFalse($treasurer->hasPermission(PermissionKey::ManageMembers));
    }

    public function test_project_viewer_is_read_only(): void
    {
        $projectViewer = collect((new GetRolePermissionMatrixAction)->execute())
            ->first(fn ($role) => $role->role === 'viewer' && $role->scope === 'project');

        $this->assertNotNull($projectViewer);
        $this->assertTrue($projectViewer->hasPermission(PermissionKey::ViewReports));
        $this->assertFalse($projectViewer->hasPermission(PermissionKey::ManageProjects));
    }

    public function test_role_permission_data_serializes_for_inertia_payloads(): void
    {
        $payload = (new GetRolePermissionMatrixAction)->execute()[0]->toArray();

        $this->assertSame('Organization Owner', $payload['label']);
        $this->assertContains('manage_members', $payload['permissions']);
    }
}
