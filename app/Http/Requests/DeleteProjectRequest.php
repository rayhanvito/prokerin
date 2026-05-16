<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Organization\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class DeleteProjectRequest extends FormRequest
{
    public function authorize(): bool
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
            ->whereIn('organization_members.role', [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
            ])
            ->exists();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
