<?php

namespace App\Models;

use App\Enums\EmployeeLeaveStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeave extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',

            'status' => EmployeeLeaveStatusEnum::class,

            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function isPending(): bool
    {
        return $this->status === EmployeeLeaveStatusEnum::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === EmployeeLeaveStatusEnum::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === EmployeeLeaveStatusEnum::REJECTED;
    }
}
