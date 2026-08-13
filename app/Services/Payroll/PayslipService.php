<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Exceptions\Payroll\EmployeePayrollNotBelongToPeriodException;
use App\Models\EmployeePayroll;
use App\Models\PayrollPeriod;

class PayslipService
{
    public function getPayslip(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll
    ): array {
        $this->ensureEmployeePayrollBelongsToPeriod(
            $period,
            $employeePayroll
        );

        $employeePayroll->load([
            'employee.jobTitle',
            'adjustments',
        ]);

        return [
            'period' => [
                'id' => $period->id,
                'year' => $period->year,
                'month' => $period->month,

                'startDate' =>
                    $period->start_date?->format('Y-m-d'),

                'endDate' =>
                    $period->end_date?->format('Y-m-d'),

                'status' =>
                    $period->status->value,
            ],

            'employee' => [
                'id' => $employeePayroll->employee->id,
                'name' => $employeePayroll->employee->name,

                'jobTitle' =>
                    $employeePayroll->employee->jobTitle
                        ? [
                            'id' =>
                                $employeePayroll
                                    ->employee
                                    ->jobTitle
                                    ->id,

                            'name' =>
                                $employeePayroll
                                    ->employee
                                    ->jobTitle
                                    ->name,
                        ]
                        : null,
            ],

            'salary' => [
                'salaryType' =>
                    $employeePayroll->salary_type->value,

                'basicSalary' =>
                    $employeePayroll->basic_salary,

                'proratedBasicSalary' =>
                    $employeePayroll->prorated_basic_salary,

                'hourlyRate' =>
                    $employeePayroll->hourly_rate,

                'hourlyEarnings' =>
                    $employeePayroll->hourly_earnings,

                'sessionRate' =>
                    $employeePayroll->session_rate,

                'sessionEarnings' =>
                    $employeePayroll->session_earnings,
            ],

            'attendance' => [
                'workedMinutes' =>
                    $employeePayroll->worked_minutes,

                'absenceDays' =>
                    $employeePayroll->absence_days,

                'unexcusedLateMinutes' =>
                    $employeePayroll->unexcused_late_minutes,

                'unexcusedEarlyLeaveMinutes' =>
                    $employeePayroll
                        ->unexcused_early_leave_minutes,

                'attendanceDeductions' =>
                    $employeePayroll->attendance_deductions,
            ],

            'sessions' => [
                'completedSessions' =>
                    $employeePayroll->completed_sessions,
            ],

            'adjustments' =>
                $employeePayroll->adjustments
                    ->map(
                        fn ($adjustment): array => [
                            'id' => $adjustment->id,
                            'type' =>
                                $adjustment->type->value,
                            'amount' =>
                                $adjustment->amount,
                            'reason' =>
                                $adjustment->reason,
                            'notes' =>
                                $adjustment->notes,
                        ]
                    )
                    ->values()
                    ->all(),

            'totals' => [
                'additionsTotal' =>
                    $employeePayroll->additions_total,

                'deductionsTotal' =>
                    $employeePayroll->deductions_total,

                'grossSalary' =>
                    $employeePayroll->gross_salary,

                'netSalary' =>
                    $employeePayroll->net_salary,
            ],

            'payablePeriod' => [
                'from' =>
                    $employeePayroll
                        ->payable_from
                        ?->format('Y-m-d'),

                'to' =>
                    $employeePayroll
                        ->payable_to
                        ?->format('Y-m-d'),

                'prorationDays' =>
                    $employeePayroll->proration_days,
            ],
        ];
    }

    private function ensureEmployeePayrollBelongsToPeriod(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll
    ): void {
        if (
            $employeePayroll->payroll_period_id !==
            $period->id
        ) {
            throw new EmployeePayrollNotBelongToPeriodException();
        }
    }
}
