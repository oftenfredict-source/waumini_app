<?php

namespace App\Http\Requests\Api\Pastor;

use App\Enums\ChurchServiceStatus;
use App\Http\Requests\Church\StoreChurchServiceRequest;

class StorePastorServiceRequest extends StoreChurchServiceRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('status')) {
            $this->merge([
                'status' => ChurchServiceStatus::Scheduled->value,
            ]);
        }
    }
}
