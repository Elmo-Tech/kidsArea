<?php

namespace App\Models;

use App\Enums\PayrollDeductionMethodEnum;
use App\Enums\PayrollProrationMethodEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'proration_method',

        'late_deduction_method',
        'early_leave_deduction_method',
        'absence_deduction_method',

        'block_finalize_on_incomplete_attendance',
    ];

    protected function casts(): array
    {
        return [
            'proration_method' =>
                PayrollProrationMethodEnum::class,

            'late_deduction_method' =>
                PayrollDeductionMethodEnum::class,

            'early_leave_deduction_method' =>
                PayrollDeductionMethodEnum::class,

            'absence_deduction_method' =>
                PayrollDeductionMethodEnum::class,

            'block_finalize_on_incomplete_attendance' =>
                'boolean',
        ];
    }
}
