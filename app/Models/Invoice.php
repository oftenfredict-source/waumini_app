<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasUuid;

    public const TYPE_INSTALLATION = 'installation';

    public const TYPE_YEARLY = 'yearly';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'church_id',
        'church_subscription_id',
        'payment_id',
        'invoice_number',
        'type',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'currency',
        'status',
        'issued_at',
        'due_at',
        'paid_at',
        'recipient_name',
        'recipient_phone',
        'recipient_location',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
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

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_INSTALLATION => __('owner.inv.installation'),
            self::TYPE_YEARLY => __('owner.inv.annual'),
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}
