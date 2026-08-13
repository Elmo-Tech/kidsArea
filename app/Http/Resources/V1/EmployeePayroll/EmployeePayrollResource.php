<?php

namespace App\Http\Resources\V1\EmployeePayroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\PayrollPaymentStatusEnum;
class EmployeePayrollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'salaryType' => $this->salary_type->value,

            'contractStartDate' =>
                $this->contract_start_date,

            'contractEndDate' =>
                $this->contract_end_date,

            'payableFrom' =>
                $this->payable_from,

            'payableTo' =>
                $this->payable_to,

            'prorationDays' =>
                $this->proration_days,

            'basicSalary' =>
                $this->basic_salary,

            'hourlyRate' =>
                $this->hourly_rate,

            'sessionRate' =>
                $this->session_rate,

            'proratedBasicSalary' =>
                $this->prorated_basic_salary,

            'workedMinutes' =>
                $this->worked_minutes,

            'hourlyEarnings' =>
                $this->hourly_earnings,

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

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at,
            'payment' => [
                'status' => $this->payment_status,

                'paidAmount' => $this->paid_amount,

                'remainingAmount' => $this->remaining_amount,
            ],
        ];
    }
}
