<?php

namespace App\Models;

use App\Enums\EmployeeAttendanceStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendance extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'employee_id',
        'attendance_date',

        'check_in_at',
        'check_out_at',

        'worked_minutes',

        'late_minutes',
        'excused_late_minutes',

        'early_leave_minutes',
        'excused_early_leave_minutes',

        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',

            'status' => EmployeeAttendanceStatusEnum::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isPresent(): bool
    {
        return $this->status ===
            EmployeeAttendanceStatusEnum::PRESENT;
    }

    public function isIncomplete(): bool
    {
        return $this->status ===
            EmployeeAttendanceStatusEnum::INCOMPLETE;
    }
}
