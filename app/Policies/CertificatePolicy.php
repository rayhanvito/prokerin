<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;

final class CertificatePolicy
{
    public function issue(User $user, int $organizationId): bool
    {
        return DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->exists();
    }

    public function view(User $user, int $organizationId): bool
    {
        $role = (string) DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->value('role');

        return in_array($role, Roles::CERTIFICATE_VIEWERS, true);
    }

    public function download(User $user, int $organizationId): bool
    {
        return $this->view($user, $organizationId);
    }
}
