<?php

declare(strict_types=1);

namespace App\Actions\Membership;

use App\Domain\Membership\PermissionKey;
use App\Domain\Organization\OrganizationRole;
use App\Domain\Project\ProjectRole;
use App\DTOs\Membership\RolePermissionData;

final class GetRolePermissionMatrixAction
{
    /**
     * @return array<int, RolePermissionData>
     */
    public function execute(): array
    {
        return [
            $this->organizationRole(OrganizationRole::Owner, [
                PermissionKey::ManageOrganization,
                PermissionKey::ManageMembers,
                PermissionKey::ManageProjects,
                PermissionKey::ManageFinance,
                PermissionKey::ApproveBudget,
                PermissionKey::ManageDocuments,
                PermissionKey::ViewReports,
            ], isSystemRole: true),
            $this->organizationRole(OrganizationRole::Admin, [
                PermissionKey::ManageMembers,
                PermissionKey::ManageProjects,
                PermissionKey::ManageDocuments,
                PermissionKey::ViewReports,
            ]),
            $this->organizationRole(OrganizationRole::Secretary, [
                PermissionKey::ManageProjects,
                PermissionKey::ManageDocuments,
                PermissionKey::ViewReports,
            ]),
            $this->organizationRole(OrganizationRole::Treasurer, [
                PermissionKey::ManageFinance,
                PermissionKey::ApproveBudget,
                PermissionKey::ViewReports,
            ]),
            $this->organizationRole(OrganizationRole::Member, [
                PermissionKey::ViewReports,
            ]),
            $this->organizationRole(OrganizationRole::Viewer, [
                PermissionKey::ViewReports,
            ]),
            $this->projectRole(ProjectRole::ProjectLead, [
                PermissionKey::ManageProjects,
                PermissionKey::ManageDocuments,
                PermissionKey::ViewReports,
            ]),
            $this->projectRole(ProjectRole::DivisionCoordinator, [
                PermissionKey::ManageProjects,
                PermissionKey::ViewReports,
            ]),
            $this->projectRole(ProjectRole::CommitteeMember, [
                PermissionKey::ViewReports,
            ]),
            $this->projectRole(ProjectRole::Viewer, [
                PermissionKey::ViewReports,
            ]),
        ];
    }

    /**
     * @param  array<int, PermissionKey>  $permissions
     */
    private function organizationRole(
        OrganizationRole $role,
        array $permissions,
        bool $isSystemRole = false,
    ): RolePermissionData {
        return new RolePermissionData(
            role: $role->value,
            label: $role->label(),
            scope: 'organization',
            permissions: $permissions,
            isSystemRole: $isSystemRole,
        );
    }

    /**
     * @param  array<int, PermissionKey>  $permissions
     */
    private function projectRole(ProjectRole $role, array $permissions): RolePermissionData
    {
        return new RolePermissionData(
            role: $role->value,
            label: $role->label(),
            scope: 'project',
            permissions: $permissions,
        );
    }
}
