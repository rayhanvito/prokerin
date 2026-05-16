<?php

declare(strict_types=1);

namespace App\Domain\Organization;

enum OrganizationRole: string
{
    case Owner = 'organization_owner';
    case Admin = 'organization_admin';
    case Secretary = 'secretary';
    case Treasurer = 'treasurer';
    case Member = 'member';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Organization Owner',
            self::Admin => 'Organization Admin',
            self::Secretary => 'Secretary',
            self::Treasurer => 'Treasurer',
            self::Member => 'Member',
            self::Viewer => 'Viewer',
        };
    }

    public function canManageOrganization(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }
}
