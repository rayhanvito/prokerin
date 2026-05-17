<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Letter\LetterType;
use App\Http\Requests\Concerns\AuthorizesActiveOrganizationRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreLetterTemplateRequest extends FormRequest
{
    use AuthorizesActiveOrganizationRoles;

    public function authorize(): bool
    {
        return $this->canActInActiveOrganization(['organization_owner', 'organization_admin']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'letter_type' => ['required', Rule::enum(LetterType::class)],
            'template_html' => ['required', 'string', 'max:60000'],
            'numbering_pattern' => ['required', 'string', 'max:160'],
            'signatory_user_id' => ['nullable', 'integer'],
        ];
    }
}
