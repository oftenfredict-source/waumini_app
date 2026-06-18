<?php

namespace App\Models;

use App\Enums\BereavementContributionType;
use App\Enums\BereavementPaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BereavementContribution extends Model
{
    protected $fillable = [
        'bereavement_event_id',
        'member_id',
        'has_contributed',
        'amount',
        'contribution_date',
        'contribution_type',
        'payment_method',
        'reference_number',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'has_contributed' => 'boolean',
            'amount' => 'decimal:2',
            'contribution_date' => 'date',
            'contribution_type' => BereavementContributionType::class,
            'payment_method' => BereavementPaymentMethod::class,
        ];
    }

    public function bereavementEvent(): BelongsTo
    {
        return $this->belongsTo(BereavementEvent::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
