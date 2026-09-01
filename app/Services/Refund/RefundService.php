<?php

declare(strict_types=1);

namespace App\Services\Refund;

use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionSourceEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Exceptions\Refund\RefundCashShiftRequiredException;
use App\Exceptions\Refund\RefundExceedsPaymentAmountException;
use App\Exceptions\Refund\RefundInsufficientCashException;
use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RefundService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(Refund::class)
            ->with([
                'payment',
                'register',
                'shift',
                'createdBy',
            ])
            ->allowedFilters([
                AllowedFilter::exact('paymentId', 'payment_id'),
                AllowedFilter::exact('cashRegisterId', 'cash_register_id'),
                AllowedFilter::exact('cashShiftId', 'cash_shift_id'),

                AllowedFilter::callback(
                    'from',
                    function ($query, $value): void {
                        $query->whereDate(
                            'refunded_at',
                            '>=',
                            $value
                        );
                    }
                ),

                AllowedFilter::callback(
                    'to',
                    function ($query, $value): void {
                        $query->whereDate(
                            'refunded_at',
                            '<=',
                            $value
                        );
                    }
                ),
            ])
            ->latest('refunded_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(Payment $payment, array $data): Refund
    {
        return DB::transaction(function () use ($payment, $data): Refund {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $amount = round((float) $data['amount'], 2);

            $refundedAmount = round(
                (float) $payment->refunds()->sum('amount'),
                2
            );

            $remainingRefundable = max(
                0,
                round(
                    (float) $payment->amount - $refundedAmount,
                    2
                )
            );

            if ($amount > $remainingRefundable) {
                throw new RefundExceedsPaymentAmountException();
            }

            $shift = null;

            if ($this->isCashPayment($payment)) {
                $shift = $this->resolveCashShift($data);

                $availableBalance = $this->calculateShiftBalance(
                    $shift
                );

                if ($amount > $availableBalance) {
                    throw new RefundInsufficientCashException();
                }
            }

            $refund = Refund::query()->create([
                'payment_id' => $payment->id,
                'cash_register_id' => $shift?->cash_register_id,
                'cash_shift_id' => $shift?->id,
                'amount' => $amount,
                'refunded_at' => $data['refundedAt'] ?? now(),
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);

            if ($shift !== null) {
                CashTransaction::query()->create([
                    'cash_register_id' => $shift->cash_register_id,
                    'cash_shift_id' => $shift->id,
                    'type' => CashTransactionTypeEnum::OUT->value,
                    'amount' => $amount,
                    'source' => CashTransactionSourceEnum::REFUND->value,
                    'sourceable_type' => Refund::class,
                    'sourceable_id' => $refund->id,
                    'transaction_at' => $refund->refunded_at,
                    'notes' => $refund->reason
                ]);
            }

            return $this->show($refund);
        });
    }

    public function show(Refund $refund): Refund
    {
        return $refund->load([
            'payment',
            'register',
            'shift',
            'cashTransaction',
            'createdBy',
        ]);
    }

    private function resolveCashShift(array $data): CashShift
    {
        if (empty($data['cashShiftId'])) {
            throw new RefundCashShiftRequiredException();
        }

        $query = CashShift::query()
            ->whereKey($data['cashShiftId'])
            ->where(
                'status',
                CashShiftStatusEnum::OPEN->value
            );

        if (! Auth::user()?->can('cash-shifts.manage-all')) {
            $query->where('opened_by', Auth::id());
        }

        $shift = $query
            ->lockForUpdate()
            ->first();

        if ($shift === null) {
            throw new RefundCashShiftRequiredException();
        }

        return $shift;
    }

    private function calculateShiftBalance(CashShift $shift): float
    {
        $totalIn = (float) $shift->transactions()
            ->where(
                'type',
                CashTransactionTypeEnum::IN->value
            )
            ->sum('amount');

        $totalOut = (float) $shift->transactions()
            ->where(
                'type',
                CashTransactionTypeEnum::OUT->value
            )
            ->sum('amount');

        return max(
            0,
            round(
                (float) $shift->opening_balance
                + $totalIn
                - $totalOut,
                2
            )
        );
    }

    private function isCashPayment(Payment $payment): bool
    {
        $paymentMethod = $payment->payment_method;

        if ($paymentMethod instanceof PaymentMethodEnum) {
            return $paymentMethod === PaymentMethodEnum::CASH;
        }

        return (int) $paymentMethod === PaymentMethodEnum::CASH->value;
    }
}
