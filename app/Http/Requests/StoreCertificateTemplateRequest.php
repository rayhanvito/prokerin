<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class StoreCertificateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $templateId = $this->route('template');

        if (is_numeric($templateId)) {
            return DB::table('certificate_templates')
                ->join('organization_members', 'organization_members.organization_id', '=', 'certificate_templates.organization_id')
                ->where('certificate_templates.id', (int) $templateId)
                ->where('organization_members.user_id', $user->id)
                ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
                ->exists();
        }

        return DB::table('organization_members')
            ->where('user_id', $user->id)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'template_html' => ['required', 'string', 'max:20000'],
            'signature_label' => ['nullable', 'string', 'max:255'],
            'signature_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'mode' => ['sometimes', Rule::in(['create', 'update'])],
        ];
    }
}
