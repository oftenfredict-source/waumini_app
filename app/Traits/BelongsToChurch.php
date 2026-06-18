<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToChurch
{
    public function scopeForChurch(Builder $query, int $churchId): Builder
    {
        return $query->where($this->getTable().'.church_id', $churchId);
    }
}
