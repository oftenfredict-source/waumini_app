<?php

namespace App\Http\Requests\Church;

use App\Enums\MemberRequestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequestRequest extends FormRequest
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
        $member = $this->user()?->member;
        $churchId = $member?->church_id;
        $isBaptismRequest = $this->input('type') === MemberRequestType::BaptismRequest->value;

        $rules = [
            'type' => ['required', Rule::enum(MemberRequestType::class)],
            'subject' => ['required', 'string', 'max:200'],
            'description' => [Rule::requiredIf(! $isBaptismRequest), 'nullable', 'string', 'max:5000'],
            'assigned_leader_id' => ['required', 'integer', 'exists:leaders,id'],
            'baptism_scope' => [Rule::requiredIf($isBaptismRequest), 'nullable', Rule::in(['self', 'children', 'both'])],
            'preferred_baptism_date' => ['nullable', 'date', 'after_or_equal:today'],
            'child_dependant_ids' => [Rule::requiredIf($isBaptismRequest && in_array($this->input('baptism_scope'), ['children', 'both'], true)), 'nullable', 'array', 'min:1'],
            'child_dependant_ids.*' => [
                'integer',
                Rule::exists('member_dependants', 'id')->where(function ($query) use ($member, $churchId) {
                    if (! $member || ! $churchId) {
                        $query->whereRaw('0 = 1');

                        return;
                    }

                    $query->where('church_id', $churchId)
                        ->whereIn('member_id', $member->familyMemberIds())
                        ->where('relationship', 'child');
                }),
            ],
        ];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'child_dependant_ids.required' => 'Select at least one child to include in the baptism request.',
            'child_dependant_ids.min' => 'Select at least one child to include in the baptism request.',
            'baptism_scope.required' => 'Specify who is requesting baptism.',
        ];
    }
}
