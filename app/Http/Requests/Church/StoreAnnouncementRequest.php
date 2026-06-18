<?php

namespace App\Http\Requests\Church;

use App\Enums\AnnouncementTargetType;
use App\Enums\AnnouncementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('announcements.manage');
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', Rule::enum(AnnouncementType::class)],
            'target_type' => ['required', Rule::enum(AnnouncementTargetType::class)],
            'department_id' => [
                Rule::requiredIf(fn () => $this->input('target_type') === AnnouncementTargetType::Department->value),
                'nullable',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'member_ids' => [
                Rule::requiredIf(fn () => $this->input('target_type') === AnnouncementTargetType::Specific->value),
                'nullable',
                'array',
                'min:1',
            ],
            'member_ids.*' => [
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'is_pinned' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active'),
            'is_pinned' => $this->has('is_pinned'),
        ]);
    }
}
