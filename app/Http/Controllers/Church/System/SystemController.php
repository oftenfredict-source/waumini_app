<?php

namespace App\Http\Controllers\Church\System;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\User;
use App\Enums\UserType;

abstract class SystemController extends Controller
{
    protected function church(): Church
    {
        return auth()->user()->church;
    }

    /**
     * @return list<int>
     */
    protected function churchUserIds(): array
    {
        return User::query()
            ->where('church_id', $this->church()->id)
            ->whereIn('user_type', array_map(fn (UserType $type) => $type->value, UserType::churchPortalTypes()))
            ->pluck('id')
            ->all();
    }
}
