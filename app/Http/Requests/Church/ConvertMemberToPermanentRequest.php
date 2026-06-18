<?php

namespace App\Http\Requests\Church;

use App\Enums\MemberType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertMemberToPermanentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('member'));
    }

    public function rules(): array
    {
        return [
            'member_type' => ['required', Rule::enum(MemberType::class)],
        ];
    }
}
