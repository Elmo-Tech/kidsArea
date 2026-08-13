<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityScheduleStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivitySchedule extends Model
{
    protected $fillable = [
        'activity_id',
        'name',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'week_days',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',

            'week_days' => 'array',

            'status' =>
                ActivityScheduleStatusEnum::class,
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class
        );
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(
            ActivitySession::class
        );
    }
}
