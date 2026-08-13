<?php

namespace App\Models;

use App\Enums\ContractStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'national_id',
        'email',
        'phone',
        'address',
        'department_id',
        'job_title_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function contracts()
    {
        return $this->hasMany(EmployeeContract::class);
    }

    public function activeContract()
    {
        return $this->hasOne(EmployeeContract::class)
            ->where('status', ContractStatusEnum::ACTIVE->value);
    }

    public function currentContract()
    {
        return $this->hasOne(EmployeeContract::class)
            ->where('status', ContractStatusEnum::ACTIVE->value)
            ->whereDate('start_date', '<=', now())
            ->where(function ($query) {
                $query
                    ->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', now());
            })
            ->latest('start_date');
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function sessions()
    {
        return $this->hasMany(EmployeeSession::class);
    }

    public function payrolls()
    {
        return $this->hasMany(EmployeePayroll::class);
    }

    public function activitySessions()
    {
        return $this->belongsToMany(
            ActivitySession::class,
            'activity_session_employees'
        );
    }
}
