<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetRolePermissionPayloadAction
{
    /**
     * @return array<int, array{role: string, label: string, scope: string, permissions: array<int, string>, isSystemRole: bool}>
     */
    public function execute(): array
    {
        return DB::table('role_permission_matrix')
            ->orderBy('id')
            ->get()
            ->map(static fn (object $role): array => [
                'role' => (string) $role->role,
                'label' => (string) $role->label,
                'scope' => (string) $role->scope,
                'permissions' => json_decode((string) $role->permissions, true) ?: [],
                'isSystemRole' => (bool) $role->is_system_role,
            ])
            ->all();
    }
}
