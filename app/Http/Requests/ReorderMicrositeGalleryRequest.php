<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesProjectMicrositeManagement;
use Illuminate\Foundation\Http\FormRequest;

final class ReorderMicrositeGalleryRequest extends FormRequest
{
    use AuthorizesProjectMicrositeManagement;

    public function authorize(): bool
    {
        return $this->canManageProjectMicrosite();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'max:80'],
            'items.*' => ['integer', 'distinct'],
        ];
    }
}
