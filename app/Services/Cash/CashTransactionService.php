<?php

declare(strict_types=1);

namespace App\Services\Cash;

use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionSourceEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Exceptions\Cash\CashShiftNotOpenException;
use App\Exceptions\Cash\CashShiftNotOwnedByCurrentUserException;
use App\Exceptions\Cash\CashTransactionInsufficientBalanceException;
use App\Exceptions\Cash\PayrollCashMustUseMainRegisterException;
use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\PayrollPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class CashTransactionService
{
    public function allForShift(CashShift $cashShift, Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(CashTransaction::class)
            ->where('cash_shift_id', $cashShift->id)
            ->with(['sourceable', 'createdBy'])
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('source'),
                AllowedFilter::callback('from', function ($query, $value): void {
                    $query->whereDate('transaction_at', '>=', $value);
                }),
                AllowedFilter::callback('to', function ($query, $value): void {
                    $query->whereDate('transaction_at', '<=', $value);
                }),
            ])
            ->latest('transaction_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function createManual(array $data): CashTransaction
    {
        return DB::transaction(function () use ($data): CashTransaction {
            $shift = CashShift::query()
                ->whereKey($data['cashShiftId'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureShiftIsOpen($shift);

            $type = CashTransactionTypeEnum::from((int) $data['type']);
            $amount = round((float) $data['amount'], 2);

            if ($type === CashTransactionTypeEnum::OUT) {
                $this->ensureSufficientShiftBalance($shift, $amount);
            }

            return CashTransaction::query()->create([
                'cash_register_id' => $shift->cash_register_id,
                'cash_shift_id' => $shift->id,
                'type' => $type->value,
                'amount' => $amount,
                'source' => CashTransactionSourceEnum::MANUAL->value,
                'sourceable_type' => null,
                'sourceable_id' => null,
                'transaction_at' => $data['transactionAt'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function createFromPayment(CashShift $shift, Payment $payment): CashTransaction
    {
        return DB::transaction(function () use ($shift, $payment): CashTransaction {
            $shift = CashShift::query()
                ->whereKey($shift->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureShiftIsOpen($shift);

            return CashTransaction::query()->firstOrCreate(
                [
                    'sourceable_type' => Payment::class,
                    'sourceable_id' => $payment->id,
                    'source' => CashTransactionSourceEnum::PAYMENT->value,
                ],
                [
                    'cash_register_id' => $shift->cash_register_id,
                    'cash_shift_id' => $shift->id,
                    'type' => CashTransactionTypeEnum::IN->value,
                    'amount' => round((float) $payment->amount, 2),
                    'transaction_at' => $payment->paid_at,
                    'notes' => null,
                ]
            );
        });
    }

    public function createFromPayrollPayment(CashRegister $register, PayrollPayment $payment): CashTransaction
    {
        return DB::transaction(function () use ($register, $payment): CashTransaction {
            $register = CashRegister::query()
                ->whereKey($register->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $register->is_main) {
                throw new PayrollCashMustUseMainRegisterException();
            }

            $existingTransaction = CashTransaction::query()
                ->where('source', CashTransactionSourceEnum::PAYROLL_PAYMENT->value)
                ->where('sourceable_type', PayrollPayment::class)
                ->where('sourceable_id', $payment->id)
                ->first();

            if ($existingTransaction !== null) {
                return $existingTransaction;
            }

            $amount = round((float) $payment->amount, 2);
            $this->ensureSufficientRegisterBalance($register, $amount);

            return CashTransaction::query()->create([
                'cash_register_id' => $register->id,
                'cash_shift_id' => null,
                'type' => CashTransactionTypeEnum::OUT->value,
                'amount' => $amount,
                'source' => CashTransactionSourceEnum::PAYROLL_PAYMENT->value,
                'sourceable_type' => PayrollPayment::class,
                'sourceable_id' => $payment->id,
                'transaction_at' => $payment->paid_at,
                'notes' => null,
            ]);
        });
    }

    private function ensureShiftIsOpen(CashShift $shift): void
    {
        if ($shift->status !== CashShiftStatusEnum::OPEN) {
            throw new CashShiftNotOpenException();
        }

        $user = Auth::user();

        if ((int) $shift->opened_by !== (int) $user?->id && ! $user?->can('cash-shifts.manage-all')) {
            throw new CashShiftNotOwnedByCurrentUserException();
        }
    }

    private function ensureSufficientShiftBalance(CashShift $shift, float $amount): void
    {
        $totalIn = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        $availableBalance = round((float) $shift->opening_balance + $totalIn - $totalOut, 2);

        if ($amount > $availableBalance) {
            throw new CashTransactionInsufficientBalanceException();
        }
    }

    private function ensureSufficientRegisterBalance(CashRegister $register, float $amount): void
    {
        $totalIn = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        $availableBalance = round($totalIn - $totalOut, 2);

        if ($amount > $availableBalance) {
            throw new CashTransactionInsufficientBalanceException();
        }
    }

    public function allForRegister(
    CashRegister $cashRegister,
    Request $request
): LengthAwarePaginator {
    $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

    return QueryBuilder::for(CashTransaction::class)
        ->where('cash_register_id', $cashRegister->id)
        ->with([
            'sourceable',
            'createdBy',
        ])
        ->allowedFilters([
            AllowedFilter::exact('type'),
            AllowedFilter::exact('source'),

            AllowedFilter::callback('from', function ($query, $value): void {
                $query->whereDate('transaction_at', '>=', $value);
            }),

            AllowedFilter::callback('to', function ($query, $value): void {
                $query->whereDate('transaction_at', '<=', $value);
            }),
        ])
        ->latest('transaction_at')
        ->latest('id')
        ->paginate($perPage);
}
}
