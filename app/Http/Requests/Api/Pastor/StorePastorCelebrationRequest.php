<?php

namespace App\Http\Requests\Api\Pastor;

use App\Http\Requests\Church\StoreCelebrationRequest;

class StorePastorCelebrationRequest extends StoreCelebrationRequest
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
}
