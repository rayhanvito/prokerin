<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class IssueCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null || ! is_numeric($this->input('template_id'))) {
            return false;
        }

        return DB::table('certificate_templates')
            ->join('organization_members', 'organization_members.organization_id', '=', 'certificate_templates.organization_id')
            ->where('certificate_templates.id', (int) $this->input('template_id'))
            ->where('organization_members.user_id', $user->id)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
            ->exists();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'template_id' => ['required', 'integer', 'exists:certificate_templates,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'meeting_id' => ['nullable', 'integer', 'exists:meetings,id'],
            'recipients' => ['required', 'array', 'min:1', 'max:100'],
            'recipients.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'recipients.*.recipient_name' => ['required', 'string', 'max:255'],
            'recipients.*.recipient_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
