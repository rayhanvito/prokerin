<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Task\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $taskId = $this->route('task');

        if ($user === null || ! is_numeric($taskId)) {
            return false;
        }

        return DB::table('project_tasks')
            ->join('projects', 'projects.id', '=', 'project_tasks.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('project_tasks.id', (int) $taskId)
            ->where('organization_members.user_id', $user->id)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'secretary'])
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ];
    }
}
