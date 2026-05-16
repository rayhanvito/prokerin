<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Project\ProjectTemplateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return DB::table('organization_members')
            ->where('user_id', $user->id)
            ->whereIn('role', ['organization_owner', 'organization_admin', 'secretary'])
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'template_type' => ['required', Rule::enum(ProjectTemplateType::class)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'project_lead_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
