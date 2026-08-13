<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityPricingTypeEnum;
use App\Enums\ActivityStatusEnum;
use App\Enums\DurationUnitEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPricingPlan extends Model
{
    protected $fillable = [
        'activity_id',
        'name',
        'type',
        'price',
        'duration_value',
        'duration_unit',
        'sessions_count',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' =>
                ActivityPricingTypeEnum::class,

            'duration_unit' =>
                DurationUnitEnum::class,

            'status' =>
                ActivityStatusEnum::class,

            'price' =>
                'decimal:2',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class
        );
    }
}
