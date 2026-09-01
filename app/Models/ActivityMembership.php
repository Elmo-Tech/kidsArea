<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityMembershipStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ActivityMembership extends Model
{
    use CreatedUpdatedBy;
    protected $fillable = [
        'child_id',
        'activity_id',
        'activity_pricing_plan_id',
        'start_date',
        'end_date',
        'sessions_total',
        'price',
        'status',
        'notes',
        'renewed_from_membership_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',

            'price' => 'decimal:2',

            'status' =>
                ActivityMembershipStatusEnum::class,
        ];
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

    public function attendances(): HasMany
    {
        return $this->hasMany(
            ActivityAttendance::class
        );
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(
            Payment::class,
            'payable'
        );
    }

    public function invoice(): MorphOne
    {
        return $this->morphOne(
            Invoice::class,
            'invoiceable'
        );
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'renewed_from_membership_id'
        );
    }

    public function renewal(): HasOne
    {
        return $this->hasOne(
            self::class,
            'renewed_from_membership_id'
        );
    }

}
