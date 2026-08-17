<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivitySessionStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ActivitySession extends Model
{
    protected $fillable = [
        'activity_id',
        'activity_schedule_id',
        'session_date',
        'start_time',
        'end_time',
        'title',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',

            'status' =>
                ActivitySessionStatusEnum::class,
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(
            Activity::class
        );
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(
            ActivitySchedule::class,
            'activity_schedule_id'
        );
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'activity_session_employees'
        );
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            Child::class,
            'activity_session_children'
        );
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(
            ActivityAttendance::class
        );
    }
}
