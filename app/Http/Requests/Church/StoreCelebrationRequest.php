<?php

namespace App\Http\Requests\Church;

use App\Enums\CelebrationStatus;
use App\Enums\CelebrationType;
use App\Enums\WeddingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCelebrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Celebration::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $type = $this->input('celebration_type');

        return [
            'celebration_type' => ['required', Rule::enum(CelebrationType::class)],
            'member_id' => ['nullable', Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId))],
            'title' => [
                Rule::requiredIf($type === CelebrationType::Other->value || ! $this->filled('member_id')),
                'nullable',
                'string',
                'max:255',
            ],
            'celebration_date' => ['required', 'date'],
            'original_date' => ['nullable', 'date'],
            'wedding_type' => [
                Rule::requiredIf($type === CelebrationType::WeddingAnniversary->value),
                'nullable',
                Rule::enum(WeddingType::class),
            ],
            'venue' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'special_guest' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
