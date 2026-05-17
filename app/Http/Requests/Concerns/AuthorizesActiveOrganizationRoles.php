<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Facades\DB;

trait AuthorizesActiveOrganizationRoles
{
    /**
     * @param  array<int, string>  $roles
     */
    private function canActInActiveOrganization(array $roles): bool
    {
        $user = $this->user();
        $activeOrganizationId = $this->session()->get('active_organization_id');

        if ($user === null) {
            return false;
        }

        return DB::table('organization_members')
            ->where('user_id', $user->id)
            ->when(is_numeric($activeOrganizationId), static function ($query) use ($activeOrganizationId): void {
                $query->where('organization_id', (int) $activeOrganizationId);
            })
            ->whereIn('role', $roles)
            ->exists();
    }
}
