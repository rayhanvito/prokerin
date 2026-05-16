<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\DashboardVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DashboardRoleResolverAction
{
    /**
     * @var array<int, array{role: string, variant: DashboardVariant}>
     */
    private const ROLE_HIERARCHY = [
        ['role' => 'organization_owner', 'variant' => DashboardVariant::Pimpinan],
        ['role' => 'organization_admin', 'variant' => DashboardVariant::Pimpinan],
        ['role' => 'secretary', 'variant' => DashboardVariant::Sekretaris],
        ['role' => 'treasurer', 'variant' => DashboardVariant::Bendahara],
        ['role' => 'project_lead', 'variant' => DashboardVariant::Operasional],
        ['role' => 'division_coordinator', 'variant' => DashboardVariant::Operasional],
        ['role' => 'member', 'variant' => DashboardVariant::Member],
        ['role' => 'viewer', 'variant' => DashboardVariant::Viewer],
    ];

    public function execute(User $user, int $organizationId): DashboardVariant
    {
        $roles = $this->roles($user, $organizationId);

        foreach (self::ROLE_HIERARCHY as $rule) {
            if (in_array($rule['role'], $roles, true)) {
                return $rule['variant'];
            }
        }

        return DashboardVariant::Viewer;
    }

    /**
     * @return array<int, string>
     */
    private function roles(User $user, int $organizationId): array
    {
        $organizationRoles = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->pluck('role')
            ->map(static fn (string $role): string => $role)
            ->all();

        $projectRoles = DB::table('project_members')
            ->join('projects', 'projects.id', '=', 'project_members.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('project_members.user_id', $user->id)
            ->pluck('project_members.role')
            ->map(static fn (string $role): string => $role)
            ->all();

        return array_values(array_unique([...$organizationRoles, ...$projectRoles]));
    }
}
