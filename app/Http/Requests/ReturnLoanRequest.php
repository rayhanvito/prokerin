<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Inventory\LoanReturnCondition;
use App\Http\Requests\Concerns\AuthorizesActiveOrganizationRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReturnLoanRequest extends FormRequest
{
    use AuthorizesActiveOrganizationRoles;

    public function authorize(): bool
    {
        return $this->canActInActiveOrganization(['organization_owner', 'organization_admin', 'secretary']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'return_condition' => ['required', Rule::enum(LoanReturnCondition::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
