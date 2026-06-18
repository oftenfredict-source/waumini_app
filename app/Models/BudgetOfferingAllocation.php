<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetOfferingAllocation extends Model
{
    protected $fillable = [
        'budget_id',
        'offering_type',
        'allocated_amount',
        'used_amount',
        'available_amount',
        'is_primary',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'used_amount' => 'decimal:2',
            'available_amount' => 'decimal:2',
            'is_primary' => 'boolean',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->allocated_amount - (float) $this->used_amount);
    }
}
