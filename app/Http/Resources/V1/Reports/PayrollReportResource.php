<?php

namespace App\Http\Resources\V1\Reports;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollReportResource extends JsonResource
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

            'salaryType' => $this->salary_type->value,

            'payablePeriod' => [
                'from' => $this->payable_from?->format('Y-m-d'),
                'to' => $this->payable_to?->format('Y-m-d'),
                'prorationDays' => $this->proration_days,
            ],

            'basicSalary' => $this->basic_salary,

            'proratedBasicSalary' =>
                $this->prorated_basic_salary,

            'hourlyRate' => $this->hourly_rate,

            'workedMinutes' =>
                $this->worked_minutes,

            'hourlyEarnings' =>
                $this->hourly_earnings,

            'sessionRate' =>
                $this->session_rate,

            'completedSessions' =>
                $this->completed_sessions,

            'sessionEarnings' =>
                $this->session_earnings,

            'absenceDays' =>
                $this->absence_days,

            'unexcusedLateMinutes' =>
                $this->unexcused_late_minutes,

            'unexcusedEarlyLeaveMinutes' =>
                $this->unexcused_early_leave_minutes,

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
