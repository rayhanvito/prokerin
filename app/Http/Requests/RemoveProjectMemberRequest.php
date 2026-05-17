<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Project\ProjectRole;
use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class RemoveProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $projectSlug = $this->route('project');

        if ($user === null || ! is_string($projectSlug)) {
            return false;
        }

        $project = DB::table('projects')
            ->where('slug', $projectSlug)
            ->first(['id', 'organization_id']);

        if ($project === null) {
            return false;
        }

        $organizationRole = DB::table('organization_members')
            ->where('organization_id', (int) $project->organization_id)
            ->where('user_id', $user->id)
            ->value('role');

        if (in_array($organizationRole, Roles::ORGANIZATION_MANAGERS, true)) {
            return true;
        }

        return DB::table('project_members')
            ->where('project_id', (int) $project->id)
            ->where('user_id', $user->id)
            ->where('role', ProjectRole::ProjectLead->value)
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
