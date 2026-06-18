<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\SpecialEvent;
use App\Models\User;

class SpecialEventService
{
    public function create(Church $church, array $data, ?User $creator = null): SpecialEvent
    {
        $data['church_id'] = $church->id;
        $data['created_by'] = $creator?->id;
        $data = $this->normalizeCategoryFields($data);

        return SpecialEvent::create($data);
    }

    public function update(SpecialEvent $event, array $data): SpecialEvent
    {
        $event->update($this->normalizeCategoryFields($data));

        return $event->fresh();
    }

    public function delete(SpecialEvent $event): void
    {
        $event->delete();
    }

    private function normalizeCategoryFields(array $data): array
    {
        if (($data['category'] ?? null) !== 'other') {
            $data['category_other'] = null;
        }

        return $data;
    }
}
