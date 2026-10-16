<?php

namespace App\Http\Requests\Api\Pastor;

use App\Enums\SpecialEventStatus;
use App\Http\Requests\Church\StoreSpecialEventRequest;

class StorePastorEventRequest extends StoreSpecialEventRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizedTimes());

        if (! $this->filled('status')) {
            $this->merge([
                'status' => SpecialEventStatus::Scheduled->value,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizedTimes(): array
    {
        $payload = [];

        foreach (['start_time', 'end_time'] as $field) {
            $value = $this->input($field);
            if (! is_string($value)) {
                continue;
            }
            $value = trim($value);
            if ($value === '') {
                $payload[$field] = null;
            } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                $payload[$field] = substr($value, 0, 5);
            }
        }

        return $payload;
    }
}
