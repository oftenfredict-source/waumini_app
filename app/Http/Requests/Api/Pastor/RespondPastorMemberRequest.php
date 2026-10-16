<?php

namespace App\Http\Requests\Api\Pastor;

use App\Enums\MemberRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondPastorMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Models\MemberRequest::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                MemberRequestStatus::InReview->value,
                MemberRequestStatus::Approved->value,
                MemberRequestStatus::Rejected->value,
                MemberRequestStatus::Completed->value,
            ])],
            'response' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
