<?php

namespace App\Models;

use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use App\Enums\AssetStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChurchAsset extends Model
{
    use BelongsToChurch, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'branch_id',
        'asset_tag',
        'name',
        'quantity',
        'batch_id',
        'category',
        'description',
        'serial_number',
        'purchase_date',
        'purchase_value',
        'current_value',
        'location',
        'condition',
        'status',
        'custodian_member_id',
        'photo_path',
        'notes',
        'recorded_by',
        'disposed_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => AssetCategory::class,
            'condition' => AssetCondition::class,
            'status' => AssetStatus::class,
            'purchase_date' => 'date',
            'disposed_at' => 'date',
            'purchase_value' => 'decimal:2',
            'current_value' => 'decimal:2',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ChurchBranch::class, 'branch_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'custodian_member_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? asset('storage/'.$this->photo_path)
            : null;
    }
}
