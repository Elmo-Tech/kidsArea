<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivitySessionEmployeeAttendanceStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySessionEmployeeAttendance extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'activity_session_id',
        'employee_id',
        'check_in_at',
        'check_out_at',
        'late_minutes',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'status' => ActivitySessionEmployeeAttendanceStatusEnum::class,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            ActivitySession::class,
            'activity_session_id'
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
