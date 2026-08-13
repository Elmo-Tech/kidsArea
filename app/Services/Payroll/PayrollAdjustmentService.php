<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Enums\PayrollAdjustmentTypeEnum;
use App\Enums\PayrollPeriodStatusEnum;
use App\Exceptions\Payroll\EmployeePayrollNotBelongToPeriodException;
use App\Exceptions\Payroll\PayrollAdjustmentNotBelongToEmployeePayrollException;
use App\Exceptions\Payroll\PayrollPeriodFinalizedException;
use App\Models\EmployeePayroll;
use App\Models\PayrollAdjustment;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;

class PayrollAdjustmentService
{
    public function createAdjustment(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll,
        array $data
    ): PayrollAdjustment {
        return DB::transaction(function () use (
            $period,
            $employeePayroll,
            $data
        ): PayrollAdjustment {
            $this->ensurePeriodIsDraft($period);

            $this->ensureEmployeePayrollBelongsToPeriod(
                $period,
                $employeePayroll
            );

            $adjustment = PayrollAdjustment::create([
                'employee_payroll_id' => $employeePayroll->id,

                'type' => $data['type'],

                'amount' => $data['amount'],

                'reason' => $data['reason'],

                'notes' => $data['notes'] ?? null,
            ]);

            $this->recalculateEmployeePayroll($employeePayroll);

            return $adjustment->refresh();
        });
    }

    public function editAdjustment(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll,
        PayrollAdjustment $adjustment
    ): PayrollAdjustment {
        $this->ensureEmployeePayrollBelongsToPeriod(
            $period,
            $employeePayroll
        );

        $this->ensureAdjustmentBelongsToEmployeePayroll(
            $employeePayroll,
            $adjustment
        );

        return $adjustment;
    }

    public function updateAdjustment(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll,
        PayrollAdjustment $adjustment,
        array $data
    ): PayrollAdjustment {
        return DB::transaction(function () use (
            $period,
            $employeePayroll,
            $adjustment,
            $data
        ): PayrollAdjustment {
            $this->ensurePeriodIsDraft($period);

            $this->ensureEmployeePayrollBelongsToPeriod(
                $period,
                $employeePayroll
            );

            $this->ensureAdjustmentBelongsToEmployeePayroll(
                $employeePayroll,
                $adjustment
            );

            $adjustment->update([
                'type' => $data['type']
                    ?? $adjustment->type->value,

                'amount' => $data['amount']
                    ?? $adjustment->amount,

                'reason' => $data['reason']
                    ?? $adjustment->reason,

                'notes' => array_key_exists('notes', $data)
                    ? $data['notes']
                    : $adjustment->notes,
            ]);

            $this->recalculateEmployeePayroll($employeePayroll);

            return $adjustment->refresh();
        });
    }

    public function deleteAdjustment(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll,
        PayrollAdjustment $adjustment
    ): bool {
        return DB::transaction(function () use (
            $period,
            $employeePayroll,
            $adjustment
        ): bool {
            $this->ensurePeriodIsDraft($period);

            $this->ensureEmployeePayrollBelongsToPeriod(
                $period,
                $employeePayroll
            );

            $this->ensureAdjustmentBelongsToEmployeePayroll(
                $employeePayroll,
                $adjustment
            );

            $deleted = (bool) $adjustment->delete();

            $this->recalculateEmployeePayroll($employeePayroll);

            return $deleted;
        });
    }

    private function recalculateEmployeePayroll(
        EmployeePayroll $employeePayroll
    ): void {
        $additionsTotal = (float) $employeePayroll
            ->adjustments()
            ->where(
                'type',
                PayrollAdjustmentTypeEnum::ADDITION->value
            )
            ->sum('amount');

        $deductionsTotal = (float) $employeePayroll
            ->adjustments()
            ->where(
                'type',
                PayrollAdjustmentTypeEnum::DEDUCTION->value
            )
            ->sum('amount');

        $grossSalary =
            (float) $employeePayroll->prorated_basic_salary
            + (float) $employeePayroll->hourly_earnings
            + (float) $employeePayroll->session_earnings
            + $additionsTotal;

        $netSalary = max(
            0,
            $grossSalary
            - (float) $employeePayroll->attendance_deductions
            - $deductionsTotal
        );

        $employeePayroll->update([
            'additions_total' => $additionsTotal,
            'deductions_total' => $deductionsTotal,

            'gross_salary' => round($grossSalary, 2),
            'net_salary' => round($netSalary, 2),
        ]);
    }

    private function ensurePeriodIsDraft(
        PayrollPeriod $period
    ): void {
        if (
            $period->status !==
            PayrollPeriodStatusEnum::DRAFT
        ) {
            throw new PayrollPeriodFinalizedException();
        }
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

    private function ensureAdjustmentBelongsToEmployeePayroll(
        EmployeePayroll $employeePayroll,
        PayrollAdjustment $adjustment
    ): void {
        if (
            $adjustment->employee_payroll_id !==
            $employeePayroll->id
        ) {
            throw new PayrollAdjustmentNotBelongToEmployeePayrollException();
        }
    }
}
