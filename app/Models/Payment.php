<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasUuid;

    protected $fillable = [
        'church_id',
        'church_subscription_id',
        'amount',
        'currency',
        'method',
        'provider',
        'provider_reference',
        'status',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ChurchSubscription::class, 'church_subscription_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
