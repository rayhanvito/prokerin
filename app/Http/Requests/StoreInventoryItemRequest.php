<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Inventory\InventoryCondition;
use App\Http\Requests\Concerns\AuthorizesActiveOrganizationRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreInventoryItemRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:160'],
            'condition' => ['required', Rule::enum(InventoryCondition::class)],
            'purchased_at' => ['nullable', 'date'],
            'purchase_amount' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
