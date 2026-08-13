<?php

namespace App\Models;

use App\Enums\PayrollPeriodStatusEnum;
use App\Enums\PayrollProrationMethodEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'year',
        'month',
        'start_date',
        'end_date',
        'proration_method',
        'status',
        'finalized_at',
        'finalized_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',

            'proration_method' =>
                PayrollProrationMethodEnum::class,

            'status' =>
                PayrollPeriodStatusEnum::class,

            'finalized_at' => 'datetime',
        ];
    }

    public function employeePayrolls()
    {
        return $this->hasMany(EmployeePayroll::class);
    }

    public function finalizedBy()
    {
        return $this->belongsTo(
            User::class,
            'finalized_by'
        );
    }
}
