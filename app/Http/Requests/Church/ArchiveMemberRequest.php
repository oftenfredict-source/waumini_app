<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('member');

        return $member && $this->user()?->can('archive', $member);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'archive_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'archive_reason.required' => 'Please provide a reason for archiving this member.',
            'archive_reason.min' => 'The archive reason must be at least 3 characters.',
        ];
    }
}
