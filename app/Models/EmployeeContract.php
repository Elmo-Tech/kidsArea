<?php

namespace App\Models;

use App\Enums\ContractStatusEnum;
use App\Enums\SalaryTypeEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeContract extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'salary_type',
        'basic_salary',
        'hourly_rate',
        'session_rate',
        'required_monthly_hours',
        'work_start_time',
        'work_end_time',
        'work_days',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',

            'salary_type' => SalaryTypeEnum::class,
            'status' => ContractStatusEnum::class,

            'basic_salary' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'session_rate' => 'decimal:2',
            'required_monthly_hours' => 'decimal:2',

            'work_days' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isActive(): bool
    {
        return $this->status === ContractStatusEnum::ACTIVE;
    }
}
