<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Facades\DB;

trait AuthorizesProjectMicrositeManagement
{
    private function canManageProjectMicrosite(): bool
    {
        $user = $this->user();
        $projectSlug = $this->route('project');

        if ($user === null || ! is_string($projectSlug)) {
            return false;
        }

        return DB::table('projects')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('projects.slug', $projectSlug)
            ->where('organization_members.user_id', $user->id)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'secretary'])
            ->exists();
    }
}
