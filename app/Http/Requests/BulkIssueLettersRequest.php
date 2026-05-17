<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesActiveOrganizationRoles;
use Illuminate\Foundation\Http\FormRequest;

final class BulkIssueLettersRequest extends FormRequest
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
            'recipient_user_ids' => ['required', 'array', 'min:1', 'max:200'],
            'recipient_user_ids.*' => ['integer', 'distinct'],
        ];
    }
}
