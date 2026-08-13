<?php

namespace App\Models;

use App\Enums\PayrollPaymentStatusEnum;
use App\Enums\SalaryTypeEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class EmployeePayroll extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'employee_contract_id',

        'contract_start_date',
        'contract_end_date',

        'payable_from',
        'payable_to',

        'proration_days',

        'salary_type',

        'basic_salary',
        'hourly_rate',
        'session_rate',

        'prorated_basic_salary',

        'worked_minutes',
        'hourly_earnings',

        'completed_sessions',
        'session_earnings',

        'absence_days',

        'unexcused_late_minutes',
        'unexcused_early_leave_minutes',

        'attendance_deductions',

        'additions_total',
        'deductions_total',

        'gross_salary',
        'net_salary',

        'notes',

        'paid_amount',
        'payment_status'
    ];

    protected function casts(): array
    {
        return [
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',

            'payable_from' => 'date',
            'payable_to' => 'date',

            'salary_type' => SalaryTypeEnum::class,

            'basic_salary' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'session_rate' => 'decimal:2',

            'prorated_basic_salary' => 'decimal:2',

            'hourly_earnings' => 'decimal:2',
            'session_earnings' => 'decimal:2',

            'attendance_deductions' => 'decimal:2',

            'additions_total' => 'decimal:2',
            'deductions_total' => 'decimal:2',

            'gross_salary' => 'decimal:2',
            'net_salary' => 'decimal:2',

            'paid_amount' => 'decimal:2',

            'payment_status' =>
                PayrollPaymentStatusEnum::class,
        ];
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract()
    {
        return $this->belongsTo(
            EmployeeContract::class,
            'employee_contract_id'
        );
    }

    public function adjustments()
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            PayrollPayment::class
        );
    }

    public function getRemainingAmountAttribute(): string
    {
        return number_format(
            max(
                0,
                (float) $this->net_salary
                - (float) $this->paid_amount
            ),
            2,
            '.',
            ''
        );
    }
}
