<?php

namespace App\Models;

use App\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAbsenceNotification extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'member_id',
        'through_service_id',
        'miss_count',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function throughService(): BelongsTo
    {
        return $this->belongsTo(ChurchService::class, 'through_service_id');
    }
}
