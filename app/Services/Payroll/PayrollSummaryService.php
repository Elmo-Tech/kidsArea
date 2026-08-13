<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Enums\SalaryTypeEnum;
use App\Models\EmployeePayroll;
use App\Models\PayrollPeriod;

class PayrollSummaryService
{
class PayrollSummaryService
{
    public function getSummary(
        PayrollPeriod $period
    ): array {
        $query = EmployeePayroll::query()
            ->where(
                'payroll_period_id',
                $period->id
            );

        $employeesCount = (clone $query)->count();

        $paidEmployeesCount = (clone $query)
            ->where(
                'payment_status',
                PayrollPaymentStatusEnum::PAID->value
            )
            ->count();

        $partiallyPaidEmployeesCount = (clone $query)
            ->where(
                'payment_status',
                PayrollPaymentStatusEnum::PARTIALLY_PAID->value
            )
            ->count();

        $unpaidEmployeesCount = (clone $query)
            ->where(
                'payment_status',
                PayrollPaymentStatusEnum::UNPAID->value
            )
            ->count();

        $paidAmountTotal = (float) (clone $query)
            ->sum('paid_amount');

        $netSalaryTotal = (float) (clone $query)
            ->sum('net_salary');

        $remainingAmountTotal = max(
            0,
            $netSalaryTotal - $paidAmountTotal
        );

        return [
            'period' => [
                'id' => $period->id,
                'year' => $period->year,
                'month' => $period->month,
                'status' => $period->status->value,
                'finalizedAt' =>
                    $period->finalized_at?->format('Y-m-d H:i:s'),
            ],

            'employeesCount' => $employeesCount,

            'salaryTypes' => [
                'monthly' => (clone $query)
                    ->where(
                        'salary_type',
                        SalaryTypeEnum::MONTHLY->value
                    )
                    ->count(),

                'hourly' => (clone $query)
                    ->where(
                        'salary_type',
                        SalaryTypeEnum::HOURLY->value
                    )
                    ->count(),

                'session' => (clone $query)
                    ->where(
                        'salary_type',
                        SalaryTypeEnum::SESSION->value
                    )
                    ->count(),

                'monthlyPlusSession' => (clone $query)
                    ->where(
                        'salary_type',
                        SalaryTypeEnum::MONTHLY_PLUS_SESSION->value
                    )
                    ->count(),
            ],

            'payments' => [
                'paidEmployeesCount' =>
                    $paidEmployeesCount,

                'partiallyPaidEmployeesCount' =>
                    $partiallyPaidEmployeesCount,

                'unpaidEmployeesCount' =>
                    $unpaidEmployeesCount,

                'paidAmountTotal' =>
                    $this->formatAmount(
                        $paidAmountTotal
                    ),

                'remainingAmountTotal' =>
                    $this->formatAmount(
                        $remainingAmountTotal
                    ),
            ],

            'earnings' => [
                'basicSalaryTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('basic_salary')
                    ),

                'proratedBasicSalaryTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('prorated_basic_salary')
                    ),

                'hourlyEarningsTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('hourly_earnings')
                    ),

                'sessionEarningsTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('session_earnings')
                    ),

                'additionsTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('additions_total')
                    ),

                'grossSalaryTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('gross_salary')
                    ),
            ],

            'deductions' => [
                'attendanceDeductionsTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('attendance_deductions')
                    ),

                'manualDeductionsTotal' =>
                    $this->formatAmount(
                        (clone $query)->sum('deductions_total')
                    ),
            ],

            'netSalaryTotal' =>
                $this->formatAmount(
                    $netSalaryTotal
                ),
        ];
    }

    private function formatAmount(
        int|float|string|null $amount
    ): string {
        return number_format(
            (float) ($amount ?? 0),
            2,
            '.',
            ''
        );
    }
}
