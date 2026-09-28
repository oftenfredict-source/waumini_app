<?php

namespace App\Models;

use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDiaryEntry extends Model
{
    use BelongsToChurch, HasUuid;

    protected $fillable = [
        'church_id',
        'member_id',
        'title',
        'body',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
