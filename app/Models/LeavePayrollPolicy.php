<?php

namespace App\Models;

use App\Enums\LeavePayrollEffectEnum;
use App\Enums\SalaryTypeEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class LeavePayrollPolicy extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'leave_type_id',
        'salary_type',
        'effect',
    ];

    protected function casts(): array
    {
        return [
            'salary_type' => SalaryTypeEnum::class,
            'effect' => LeavePayrollEffectEnum::class,
        ];
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
