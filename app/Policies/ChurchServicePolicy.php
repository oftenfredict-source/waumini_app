<?php

namespace App\Policies;

use App\Models\ChurchService;
use App\Models\User;

class ChurchServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('services.view');
    }

    public function view(User $user, ChurchService $churchService): bool
    {
        return $user->isChurchUser()
            && $user->can('services.view')
            && $churchService->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('services.manage');
    }

    public function update(User $user, ChurchService $churchService): bool
    {
        return $user->isChurchUser()
            && $user->can('services.manage')
            && $churchService->church_id === $user->church_id;
    }

    public function delete(User $user, ChurchService $churchService): bool
    {
        return $this->update($user, $churchService);
    }
}
