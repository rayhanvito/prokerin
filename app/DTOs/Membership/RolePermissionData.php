<?php

declare(strict_types=1);

namespace App\DTOs\Membership;

use App\Domain\Membership\PermissionKey;

final readonly class RolePermissionData
{
    /**
     * @param  array<int, PermissionKey>  $permissions
     */
    public function __construct(
        public string $role,
        public string $label,
        public string $scope,
        public array $permissions,
        public bool $isSystemRole = false,
    ) {}

    public function hasPermission(PermissionKey $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    /**
     * @return array{role: string, label: string, scope: string, permissions: array<int, string>, isSystemRole: bool}
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'label' => $this->label,
            'scope' => $this->scope,
            'permissions' => array_map(
                static fn (PermissionKey $permission): string => $permission->value,
                $this->permissions,
            ),
            'isSystemRole' => $this->isSystemRole,
        ];
    }
}
