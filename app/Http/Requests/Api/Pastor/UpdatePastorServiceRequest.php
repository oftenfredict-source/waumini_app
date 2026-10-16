<?php

namespace App\Http\Requests\Api\Pastor;

use App\Http\Requests\Church\UpdateChurchServiceRequest;
use App\Models\ChurchService;

class UpdatePastorServiceRequest extends UpdateChurchServiceRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['start_time', 'end_time'] as $field) {
            $value = $this->input($field);
            if (is_string($value) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                $this->merge([$field => substr($value, 0, 5)]);
            }
        }
    }

    public function authorize(): bool
    {
        $service = $this->serviceRecord();

        if ($service) {
            $this->route()->setParameter('service', $service);
        }

        return $service instanceof ChurchService && $this->user()->can('update', $service);
    }

    public function serviceRecord(): ?ChurchService
    {
        $existing = $this->route('service');
        if ($existing instanceof ChurchService) {
            return $existing;
        }

        $id = (int) $this->route('id');
        if ($id < 1 || ! $this->user()?->church_id) {
            return null;
        }

        return ChurchService::forChurch($this->user()->church_id)->find($id);
    }
}
