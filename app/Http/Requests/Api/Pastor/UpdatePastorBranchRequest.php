<?php

namespace App\Http\Requests\Api\Pastor;

use App\Models\ChurchBranch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePastorBranchRequest extends FormRequest
{
    private ?ChurchBranch $branchModel = null;

    public function authorize(): bool
    {
        $branch = $this->branchModel();

        return $branch !== null && $this->user()->can('update', $branch);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $branch = $this->branchModel();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('church_branches', 'code')
                    ->where(fn ($q) => $q->where('church_id', $churchId))
                    ->ignore($branch?->id),
            ],
            'is_headquarters' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'pastor_name' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    public function branchModel(): ?ChurchBranch
    {
        if ($this->branchModel !== null) {
            return $this->branchModel;
        }

        $id = (int) $this->route('id');

        return $this->branchModel = ChurchBranch::forChurch($this->user()->church_id)->find($id);
    }
}
