<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityUsageStatusEnum;
use App\Enums\ActivityUsageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityUsage extends Model
{
    protected $fillable = [
        'visit_id',
        'child_id',
        'activity_id',
        'activity_pricing_plan_id',
        'usage_type',
        'started_at',
        'ended_at',
        'planned_duration_minutes',
        'planned_end_at',
        'total_paused_minutes',
        'duration_minutes',
        'hourly_price',
        'expected_amount',
        'final_amount',
        'status',
        'started_by',
        'closed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'usage_type' =>
                ActivityUsageTypeEnum::class,

            'status' =>
                ActivityUsageStatusEnum::class,

            'started_at' =>
                'datetime',

            'ended_at' =>
                'datetime',

            'planned_end_at' =>
                'datetime',

            'hourly_price' =>
                'decimal:2',

            'expected_amount' =>
                'decimal:2',

            'final_amount' =>
                'decimal:2',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(
            Visit::class
        );
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(
            Child::class
        );
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class
        );
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(
            ActivityPricingPlan::class,
            'activity_pricing_plan_id'
        );
    }

    public function pauses(): HasMany
    {
        return $this->hasMany(
            ActivityUsagePause::class
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
}
