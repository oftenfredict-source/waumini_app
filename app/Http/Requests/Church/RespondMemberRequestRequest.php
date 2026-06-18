<?php

namespace App\Http\Requests\Church;

use App\Enums\MemberRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondMemberRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(MemberRequestStatus::class)],
            'response' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
