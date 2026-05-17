<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Project\ProjectStatus;
use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class TransitionProjectStatusRequest extends FormRequest
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
            ->whereIn('organization_members.role', Roles::PROJECT_LEADERSHIP)
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ProjectStatus::class)],
        ];
    }
}
