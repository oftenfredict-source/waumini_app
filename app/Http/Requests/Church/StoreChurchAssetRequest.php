<?php

namespace App\Http\Requests\Church;

use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Models\ChurchAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChurchAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ChurchAsset::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $branchesEnabled = (bool) $this->user()->church?->branches_enabled;
        $isDisposed = $this->input('status') === AssetStatus::Disposed->value;

        return [
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'registration_mode' => ['required', Rule::in(['lot', 'individual'])],
            'category' => ['required', Rule::enum(AssetCategory::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'purchase_value' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'condition' => ['required', Rule::enum(AssetCondition::class)],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'disposed_at' => [Rule::requiredIf($isDisposed), 'nullable', 'date', 'before_or_equal:today'],
            'branch_id' => $branchesEnabled ? [
                'nullable',
                'integer',
                Rule::exists('church_branches', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ] : ['nullable', 'prohibited'],
            'custodian_member_id' => [
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'photo' => ['nullable', 'image', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
