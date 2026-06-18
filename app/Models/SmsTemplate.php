<?php

namespace App\Models;

use App\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsTemplate extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'key',
        'body',
        'is_active',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function label(): string
    {
        return (string) (config('sms_templates.templates.'.$this->key.'.label') ?? $this->key);
    }

    /**
     * @return list<string>
     */
    public function placeholders(): array
    {
        return config('sms_templates.templates.'.$this->key.'.placeholders', []);
    }
}
