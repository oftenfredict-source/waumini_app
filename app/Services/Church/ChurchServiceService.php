<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\ChurchService;
use App\Models\User;

class ChurchServiceService
{
    public function create(Church $church, array $data, ?User $creator = null): ChurchService
    {
        $data['church_id'] = $church->id;
        $data['created_by'] = $creator?->id;

        if (($data['service_type'] ?? null) !== 'extra') {
            $data['title'] = null;
        }

        return ChurchService::create($data);
    }

    public function update(ChurchService $service, array $data): ChurchService
    {
        if (($data['service_type'] ?? null) !== 'extra') {
            $data['title'] = null;
        }

        $service->update($data);

        return $service->fresh();
    }

    public function delete(ChurchService $service): void
    {
        $service->delete();
    }
}
