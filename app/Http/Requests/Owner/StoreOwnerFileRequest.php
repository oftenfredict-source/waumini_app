<?php

namespace App\Http\Requests\Owner;

use App\Models\OwnerFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOwnerFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', OwnerFile::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', Rule::in(OwnerFile::CATEGORIES)],
            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,rtf,jpg,jpeg,png,gif,webp,zip,rar,7z',
            ],
        ];
    }
}
