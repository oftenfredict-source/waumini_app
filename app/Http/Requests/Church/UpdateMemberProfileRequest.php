<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessMemberPortal() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone_number' => ['nullable', 'string', 'max:30'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
