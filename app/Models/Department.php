<?php

namespace App\Models;

use App\Enums\DepartmentStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use BelongsToChurch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'name',
        'description',
        'head_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DepartmentStatus::class,
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'head_id');
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'department_member')
            ->withPivot('role')
            ->withTimestamps();
    }
}
