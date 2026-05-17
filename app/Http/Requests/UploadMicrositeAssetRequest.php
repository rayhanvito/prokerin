<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesProjectMicrositeManagement;
use Illuminate\Foundation\Http\FormRequest;

final class UploadMicrositeAssetRequest extends FormRequest
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
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:160'],
        ];
    }
}
