<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $contract = $this->activeContract ?? null;

        return [
            'id' => $this->id,

            'name' => $this->name,
            'nationalId' => $this->national_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,

            'department' => $this->department
                ? [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ]
                : null,

            'jobTitle' => $this->jobTitle
                ? [
                    'id' => $this->jobTitle->id,
                    'name' => $this->jobTitle->name,
                ]
                : null,

            // 'contract' => $contract
            //     ? [
            //         'id' => $contract->id,
            //         'salaryType' => $contract->salary_type->value,
            //         'startDate' => $contract->start_date?->format('Y-m-d'),
            //         'endDate' => $contract->end_date?->format('Y-m-d'),
            //         'basicSalary' => $contract->basic_salary,
            //         'hourlyRate' => $contract->hourly_rate,
            //         'sessionRate' => $contract->session_rate,
            //         'requiredMonthlyHours' => $contract->required_monthly_hours,
            //         'workStartTime' => $contract->work_start_time,
            //         'workEndTime' => $contract->work_end_time,
            //         'workDays' => $contract->work_days,
            //         'status' => $contract->status->value,
            //     ]
            //     : null,

            'user' => $this->user
                ? [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                ]
                : null,
        ];
    }
}
