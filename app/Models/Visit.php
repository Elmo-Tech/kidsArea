<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{

    use CreatedUpdatedBy;
    protected $fillable = [
        'child_id',
        'started_at',
        'closed_at',
        'status',
        'started_by',
        'closed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'closed_at' => 'datetime',

            'status' =>
                VisitStatusEnum::class,
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(
            Child::class
        );
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'started_by'
        );
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'closed_by'
        );
    }

    public function activityUsages(): HasMany
    {
        return $this->hasMany(
            ActivityUsage::class
        );
    }

    public function cafeOrders(): HasMany
    {
        return $this->hasMany(
            CafeOrder::class
        );
    }


    public function checkout(): HasOne
    {
        return $this->hasOne(VisitCheckout::class);
    }
}
