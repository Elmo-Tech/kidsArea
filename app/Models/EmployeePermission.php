<?php

namespace App\Models;

use App\Enums\EmployeePermissionStatusEnum;
use App\Enums\EmployeePermissionTypeEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
class EmployeePermission extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'employee_id',
        'permission_date',
        'type',
        'from_time',
        'to_time',
        'minutes',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'permission_date' => 'date',

            'type' => EmployeePermissionTypeEnum::class,
            'status' => EmployeePermissionStatusEnum::class,

            'approved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
        return $this->status === EmployeePermissionStatusEnum::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === EmployeePermissionStatusEnum::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === EmployeePermissionStatusEnum::REJECTED;
    }

    public function formatTime(?string $time): ?string
    {
        return $time
            ? substr($time, 0, 5)
            : null;
    }

}
