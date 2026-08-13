<?php

namespace App\Models;

use App\Enums\EmployeeSessionStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSession extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'employee_id',
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
            'status' => EmployeeSessionStatusEnum::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isPending(): bool
    {
        return $this->status === EmployeeSessionStatusEnum::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === EmployeeSessionStatusEnum::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === EmployeeSessionStatusEnum::CANCELLED;
    }
}
