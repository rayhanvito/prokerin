<?php

declare(strict_types=1);

namespace App\Support;

final class Roles
{
    public const ORGANIZATION_OWNER = 'organization_owner';

    public const ORGANIZATION_MANAGERS = ['organization_owner', 'organization_admin'];

    public const ORGANIZATION_FULL_VIEWERS = [
        'organization_owner',
        'organization_admin',
        'secretary',
        'treasurer',
        'project_lead',
        'division_coordinator',
        'member',
        'viewer',
    ];

    public const FINANCE_VIEWERS = ['organization_owner', 'organization_admin', 'treasurer'];

    public const FINANCE_MANAGERS = ['organization_owner', 'organization_admin', 'treasurer'];

    public const SECRETARY_AND_UP = ['organization_owner', 'organization_admin', 'secretary'];

    public const PROJECT_LEADERSHIP = [
        'organization_owner',
        'organization_admin',
        'project_lead',
        'division_coordinator',
    ];

    public const INVITABLE_ORGANIZATION_ROLES = [
        'secretary',
        'treasurer',
        'project_lead',
        'division_coordinator',
        'member',
        'viewer',
    ];
}
