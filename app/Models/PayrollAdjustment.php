<?php

namespace App\Models;

use App\Enums\PayrollAdjustmentTypeEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustment extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'employee_payroll_id',
        'type',
        'amount',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => PayrollAdjustmentTypeEnum::class,
            'amount' => 'decimal:2',
        ];
    }

    public function employeePayroll()
    {
        return $this->belongsTo(EmployeePayroll::class);
    }
}
