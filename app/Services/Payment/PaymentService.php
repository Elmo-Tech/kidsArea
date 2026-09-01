<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Enums\ActivityMembershipStatusEnum;
use App\Enums\ActivityUsageStatusEnum;
use App\Enums\CafeOrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\VisitCheckoutStatusEnum;
use App\Enums\VisitPaymentStatusEnum;
use App\Exceptions\Activity\CancelledMembershipCannotReceivePaymentException;
use App\Exceptions\Payment\ActivityUsageNotClosedException;
use App\Exceptions\Payment\CafeOrderNotCompletedException;
use App\Exceptions\Payment\PayableAlreadyPaidException;
use App\Exceptions\Payment\PaymentExceedsRemainingAmountException;
use App\Exceptions\Payment\StandalonePaymentNotAllowedException;
use App\Exceptions\VisitPayment\VisitCheckoutCancelledException;
use App\Exceptions\VisitPayment\VisitCheckoutNotFinalizedException;
use App\Models\ActivityMembership;
use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\CashShift;
use App\Models\Payment;
use App\Models\VisitCheckout;
use App\Services\Cash\CashTransactionService;
use App\Services\Invoice\InvoiceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly CashTransactionService $cashTransactionService
    ) {}

    public function createPayment(Model $payable, array $data): Payment
    {
        return DB::transaction(function () use ($payable, $data): Payment {
            $payable = $this->lockPayable($payable);
            $this->ensurePayableCanReceivePayment($payable);

            $total = $this->getPayableTotal($payable);
            $paidAmount = $this->getPaidAmount($payable);
            $remainingAmount = round($total - $paidAmount, 2);
            $amount = round((float) $data['amount'], 2);

            if ($remainingAmount <= 0) {
                throw new PayableAlreadyPaidException();
            }

            if ($amount > $remainingAmount) {
                throw new PaymentExceedsRemainingAmountException();
            }

            $isCash = (int) $data['paymentMethod'] === PaymentMethodEnum::CASH->value;

            $payment = $payable->payments()->create([
                'amount' => $amount,
                'payment_method' => $data['paymentMethod'],
                'cash_shift_id' => $isCash ? $data['cashShiftId'] : null,
                'paid_at' => $data['paidAt'] ?? now(),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($isCash) {
                $shift = CashShift::query()
                    ->whereKey($data['cashShiftId'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->cashTransactionService->createFromPayment($shift, $payment);
            }

            $this->ensureInvoiceIfPaid($payable);

            return $payment;
        });
    }

    public function getPaymentSummary(Model $payable): array
    {
        $total = $this->getPayableTotal($payable);
        $paidAmount = $this->getPaidAmount($payable);
        $remainingAmount = max(0, round($total - $paidAmount, 2));

        return [
            'total' => round($total, 2),
            'paidAmount' => round($paidAmount, 2),
            'remainingAmount' => $remainingAmount,
            'paymentStatus' => $this->resolvePaymentStatus($total, $paidAmount)->value,
        ];
    }

    public function getPaidAmount(Model $payable): float
    {
        $payments = $payable->payments()->withSum('refunds', 'amount')->get();

        $paidAmount = $payments->sum(function (Payment $payment): float {
            return max(
                0,
                (float) $payment->amount - (float) ($payment->refunds_sum_amount ?? 0)
            );
        });

        return round((float) $paidAmount, 2);
    }

    public function ensureInvoiceIfPaid(Model $payable): void
    {
        $summary = $this->getPaymentSummary($payable);

        if ($summary['paymentStatus'] === VisitPaymentStatusEnum::PAID->value) {
            $this->invoiceService->createFor($payable);
        }
    }

    private function getPayableTotal(Model $payable): float
    {
        return match (true) {
            $payable instanceof VisitCheckout => (float) $payable->total,
            $payable instanceof ActivityUsage => (float) $payable->final_amount,
            $payable instanceof CafeOrder => (float) $payable->total,
            $payable instanceof ActivityMembership => (float) $payable->price,
            default => throw new \InvalidArgumentException('Unsupported payable model.'),
        };
    }

    private function ensurePayableCanReceivePayment(Model $payable): void
    {
        if ($payable instanceof VisitCheckout) {
            $this->ensureVisitCheckoutCanReceivePayment($payable);
            return;
        }

        if ($payable instanceof ActivityUsage) {
            $this->ensureActivityUsageCanReceivePayment($payable);
            return;
        }

        if ($payable instanceof CafeOrder) {
            $this->ensureCafeOrderCanReceivePayment($payable);
            return;
        }

        if ($payable instanceof ActivityMembership) {
            $this->ensureActivityMembershipCanReceivePayment($payable);
            return;
        }

        throw new \InvalidArgumentException('Unsupported payable model.');
    }

    private function ensureVisitCheckoutCanReceivePayment(VisitCheckout $checkout): void
    {
        if ($checkout->status === VisitCheckoutStatusEnum::CANCELLED) {
            throw new VisitCheckoutCancelledException();
        }

        if ($checkout->status !== VisitCheckoutStatusEnum::FINALIZED) {
            throw new VisitCheckoutNotFinalizedException();
        }
    }

    private function ensureActivityUsageCanReceivePayment(ActivityUsage $usage): void
    {
        if ($usage->visit_id !== null) {
            throw new StandalonePaymentNotAllowedException();
        }

        if ($usage->status !== ActivityUsageStatusEnum::CLOSED) {
            throw new ActivityUsageNotClosedException();
        }

        if ($usage->final_amount === null) {
            throw new ActivityUsageNotClosedException();
        }
    }

    private function ensureCafeOrderCanReceivePayment(CafeOrder $order): void
    {
        if ($order->visit_id !== null) {
            throw new StandalonePaymentNotAllowedException();
        }

        if ($order->status !== CafeOrderStatusEnum::COMPLETED) {
            throw new CafeOrderNotCompletedException();
        }
    }

    private function ensureActivityMembershipCanReceivePayment(ActivityMembership $membership): void
    {
        if ($membership->status === ActivityMembershipStatusEnum::CANCELLED) {
            throw new CancelledMembershipCannotReceivePaymentException();
        }
    }

    private function resolvePaymentStatus(float $total, float $paidAmount): VisitPaymentStatusEnum
    {
        if ($total <= 0) {
            return VisitPaymentStatusEnum::PAID;
        }

        if ($paidAmount <= 0) {
            return VisitPaymentStatusEnum::UNPAID;
        }

        if ($paidAmount < $total) {
            return VisitPaymentStatusEnum::PARTIALLY_PAID;
        }

        return VisitPaymentStatusEnum::PAID;
    }

    private function lockPayable(Model $payable): Model
    {
        return $payable::query()
            ->whereKey($payable->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }
}
