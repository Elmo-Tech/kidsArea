<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Enums\PaymentMethodEnum;
use App\Enums\PayrollPaymentStatusEnum;
use App\Enums\PayrollPeriodStatusEnum;
use App\Exceptions\PayrollPayment\EmployeePayrollAlreadyPaidException;
use App\Exceptions\PayrollPayment\InvalidPayrollPaymentAmountException;
use App\Exceptions\PayrollPayment\PayrollPeriodNotFinalizedException;
use App\Models\CashRegister;
use App\Models\EmployeePayroll;
use App\Models\PayrollPayment;
use App\Services\Cash\CashTransactionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollPaymentService
{
    public function __construct(
        private readonly CashTransactionService $cashTransactionService
    ) {}

    public function allForEmployeePayroll(
        EmployeePayroll $employeePayroll,
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return PayrollPayment::query()
            ->where('employee_payroll_id', $employeePayroll->id)
            ->with(['createdBy', 'cashShift.register'])
            ->latest('paid_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function payRemainingSalary(
        EmployeePayroll $employeePayroll,
        array $data = []
    ): PayrollPayment {
        return DB::transaction(function () use ($employeePayroll, $data): PayrollPayment {
            $employeePayroll = EmployeePayroll::query()
                ->with('payrollPeriod')
                ->whereKey($employeePayroll->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensurePeriodIsFinalized($employeePayroll);
            $this->syncPaymentSummary($employeePayroll);

            if ($employeePayroll->payment_status === PayrollPaymentStatusEnum::PAID) {
                throw new EmployeePayrollAlreadyPaidException();
            }

            $remainingAmount = $this->calculateRemainingAmount($employeePayroll);

            if ($remainingAmount <= 0) {
                throw new InvalidPayrollPaymentAmountException();
            }

            $paymentMethod = PaymentMethodEnum::from((int) $data['paymentMethod']);

            $payment = PayrollPayment::query()->create([
                'employee_payroll_id' => $employeePayroll->id,
                'amount' => $remainingAmount,
                'payment_method' => $paymentMethod->value,
                'cash_shift_id' => null,
                'paid_at' => now(),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($paymentMethod === PaymentMethodEnum::CASH) {
                $mainRegister = CashRegister::query()
                    ->where('is_main', true)
                    ->firstOrFail();

                $this->cashTransactionService->createFromPayrollPayment(
                    $mainRegister,
                    $payment
                );
            }

            $this->syncPaymentSummary($employeePayroll);

            return $payment->load(['createdBy', 'cashShift.register']);
        });
    }

    public function syncPaymentSummary(EmployeePayroll $employeePayroll): EmployeePayroll
    {
        $paidAmount = (float) $employeePayroll->payments()->sum('amount');
        $netSalary = (float) $employeePayroll->net_salary;
        $paymentStatus = $this->resolvePaymentStatus($netSalary, $paidAmount);

        $employeePayroll->update([
            'paid_amount' => round($paidAmount, 2),
            'payment_status' => $paymentStatus->value,
        ]);

        return $employeePayroll->refresh();
    }

    private function calculateRemainingAmount(EmployeePayroll $employeePayroll): float
    {
        $remainingAmount = (float) $employeePayroll->net_salary - (float) $employeePayroll->paid_amount;

        return round(max(0, $remainingAmount), 2);
    }

    private function resolvePaymentStatus(
        float $netSalary,
        float $paidAmount
    ): PayrollPaymentStatusEnum {
        if ($paidAmount <= 0) {
            return PayrollPaymentStatusEnum::UNPAID;
        }

        if ($paidAmount < $netSalary) {
            return PayrollPaymentStatusEnum::PARTIALLY_PAID;
        }

        return PayrollPaymentStatusEnum::PAID;
    }

    private function ensurePeriodIsFinalized(EmployeePayroll $employeePayroll): void
    {
        if ($employeePayroll->payrollPeriod->status !== PayrollPeriodStatusEnum::FINALIZED) {
            throw new PayrollPeriodNotFinalizedException();
        }
    }
}
