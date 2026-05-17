<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesActiveOrganizationRoles;
use Illuminate\Foundation\Http\FormRequest;

final class DraftLetterRequest extends FormRequest
{
    use AuthorizesActiveOrganizationRoles;

    public function authorize(): bool
    {
        return $this->canActInActiveOrganization(['organization_owner', 'organization_admin', 'secretary']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'template_id' => ['required', 'integer'],
            'project_id' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:200'],
            'recipient_name' => ['required', 'string', 'max:180'],
            'recipient_organization' => ['nullable', 'string', 'max:180'],
            'body_data' => ['nullable', 'array'],
            'body_data.*' => ['nullable', 'string', 'max:500'],
        ];
    }
}
