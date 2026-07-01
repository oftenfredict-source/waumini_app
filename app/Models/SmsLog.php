<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = [
        'church_id',
        'recipient',
        'context',
        'message',
        'segments',
        'status',
        'provider_response',
        'edited_at',
        'edited_by',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function contextLabel(): string
    {
        return (string) (config('sms_templates.context_labels.'.$this->context) ?? $this->context);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'sent' => 'success',
            'failed' => 'danger',
            'skipped' => 'warning',
            default => 'secondary',
        };
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public static function monthlyCountForChurch(int $churchId): int
    {
        return static::query()
            ->where('church_id', $churchId)
            ->where('status', 'sent')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    public static function monthlySegmentsForChurch(int $churchId): int
    {
        return (int) static::query()
            ->where('church_id', $churchId)
            ->where('status', 'sent')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('segments');
    }
}
