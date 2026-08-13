<?php

namespace App\Http\Resources\V1\EmployeePayroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllEmployeePayrollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'employee' => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,

                'jobTitle' => $this->employee->jobTitle
                    ? [
                        'id' => $this->employee->jobTitle->id,
                        'name' => $this->employee->jobTitle->name,
                    ]
                    : null,
            ],

            'salaryType' =>
                $this->salary_type->value,

            'payableFrom' =>
                $this->payable_from?->format('Y-m-d'),

            'payableTo' =>
                $this->payable_to?->format('Y-m-d'),

            'prorationDays' =>
                $this->proration_days,

            'proratedBasicSalary' =>
                $this->prorated_basic_salary,

            'hourlyEarnings' =>
                $this->hourly_earnings,

            'completedSessions' =>
                $this->completed_sessions,

            'sessionEarnings' =>
                $this->session_earnings,

            'absenceDays' =>
                $this->absence_days,

            'attendanceDeductions' =>
                $this->attendance_deductions,

            'additionsTotal' =>
                $this->additions_total,

            'deductionsTotal' =>
                $this->deductions_total,

            'grossSalary' =>
                $this->gross_salary,

            'netSalary' =>
                $this->net_salary,
        ];
    }
}
