<?php

namespace App\Http\Requests\Api\Pastor;

use App\Models\SpecialEvent;

class UpdatePastorEventRequest extends StorePastorEventRequest
{
    public function authorize(): bool
    {
        $event = $this->eventRecord();

        if ($event) {
            $this->route()->setParameter('special_event', $event);
        }

        return $event instanceof SpecialEvent && $this->user()->can('update', $event);
    }

    public function eventRecord(): ?SpecialEvent
    {
        $existing = $this->route('special_event');
        if ($existing instanceof SpecialEvent) {
            return $existing;
        }

        $id = (int) $this->route('id');
        if ($id < 1 || ! $this->user()?->church_id) {
            return null;
        }

        return SpecialEvent::forChurch($this->user()->church_id)->find($id);
    }
}
