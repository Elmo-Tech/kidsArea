<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Enums\CashRegisterStatusEnum;
use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Exceptions\Cash\CashRegisterAlreadyHasOpenShiftException;
use App\Exceptions\Cash\CashRegisterInactiveException;
use App\Exceptions\Cash\CashShiftAlreadyClosedException;
use App\Exceptions\Cash\CashShiftNotOwnedByCurrentUserException;
use App\Models\CashRegister;
use App\Models\CashShift;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Cash\CashRegisterHasPendingTransferException;

class EmployeeCashShiftService
{
    public function current(): ?CashShift
    {
        return CashShift::query()
            ->where('opened_by', Auth::id())
            ->where('status', CashShiftStatusEnum::OPEN->value)
            ->with([
                'register',
                'openedBy',
                'closedBy',
            ])
            ->latest('id')
            ->first();
    }

public function open(array $data): CashShift
{
    return DB::transaction(function () use ($data): CashShift {
        $this->ensureUserCanOpenCashShift();

        $register = CashRegister::query()
            ->whereKey($data['cashRegisterId'])
            ->where('is_main', false)
            ->lockForUpdate()
            ->firstOrFail();

        if ($register->status !== CashRegisterStatusEnum::ACTIVE) {
            throw new CashRegisterInactiveException();
        }

        $userHasOpenShift = CashShift::query()
            ->where('opened_by', Auth::id())
            ->where('status', CashShiftStatusEnum::OPEN->value)
            ->exists();

        if ($userHasOpenShift) {
            throw new CashRegisterAlreadyHasOpenShiftException();
        }

        $registerHasOpenShift = CashShift::query()
            ->where('cash_register_id', $register->id)
            ->where('status', CashShiftStatusEnum::OPEN->value)
            ->exists();

        if ($registerHasOpenShift) {
            throw new CashRegisterAlreadyHasOpenShiftException();
        }

        $userHasPendingTransfer = CashShift::query()
            ->where('opened_by', Auth::id())
            ->where('status', CashShiftStatusEnum::CLOSED->value)
            ->whereNotNull('actual_closing_balance')
            ->where('actual_closing_balance', '>', 0)
            ->whereDoesntHave('transfer')
            ->exists();

        if ($userHasPendingTransfer) {
            throw new CashRegisterHasPendingTransferException();
        }

        $registerHasPendingTransfer = CashShift::query()
            ->where('cash_register_id', $register->id)
            ->where('status', CashShiftStatusEnum::CLOSED->value)
            ->whereNotNull('actual_closing_balance')
            ->where('actual_closing_balance', '>', 0)
            ->whereDoesntHave('transfer')
            ->exists();

        if ($registerHasPendingTransfer) {
            throw new CashRegisterHasPendingTransferException();
        }

        $shift = CashShift::create([
            'cash_register_id' => $register->id,
            'opened_by' => Auth::id(),
            'opened_at' => now(),
            'opening_balance' => $data['openingBalance'],
            'status' => CashShiftStatusEnum::OPEN->value,
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->loadShift($shift);
    });
}
    public function close(array $data): CashShift
    {
        return DB::transaction(function () use ($data): CashShift {
            $shift = CashShift::query()
                ->where('opened_by', Auth::id())
                ->where('status', CashShiftStatusEnum::OPEN->value)
                ->lockForUpdate()
                ->first();

            if (! $shift) {
                throw new CashShiftAlreadyClosedException();
            }

            $this->ensureUserCanManageShift($shift);

            $expectedClosingBalance = $this->calculateExpectedBalance($shift);
            $actualClosingBalance = round((float) $data['actualClosingBalance'], 2);
            $difference = round($actualClosingBalance - $expectedClosingBalance, 2);

            $shift->update([
                'closed_by' => Auth::id(),
                'closed_at' => now(),
                'expected_closing_balance' => $expectedClosingBalance,
                'actual_closing_balance' => $actualClosingBalance,
                'difference' => $difference,
                'status' => CashShiftStatusEnum::CLOSED->value,
                'notes' => array_key_exists('notes', $data)
                    ? $data['notes']
                    : $shift->notes,
            ]);

            return $this->loadShift($shift->refresh());
        });
    }

    private function ensureUserCanOpenCashShift(): void
    {
        $user = Auth::user();

        if (! $user?->can('cash-shifts.employee-open')) {
            throw new AuthorizationException(
                __('cash.not_allowed_to_open_shift')
            );
        }
    }

    private function ensureUserCanManageShift(CashShift $shift): void
    {
        $user = Auth::user();

        if (
            (int) $shift->opened_by !== (int) $user?->id
            && ! $user?->can('cash-shifts.manage-all')
        ) {
            throw new CashShiftNotOwnedByCurrentUserException();
        }
    }

    private function calculateExpectedBalance(CashShift $shift): float
    {
        $totalIn = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        return round(
            (float) $shift->opening_balance
            + $totalIn
            - $totalOut,
            2
        );
    }

    private function loadShift(CashShift $shift): CashShift
    {
        return $shift->load([
            'register',
            'openedBy',
            'closedBy',
        ]);
    }

    public function pendingTransfer(): ?CashShift
    {
        return CashShift::query()
            ->where('opened_by', Auth::id())
            ->where('status', CashShiftStatusEnum::CLOSED->value)
            ->whereNotNull('actual_closing_balance')
            ->where('actual_closing_balance', '>', 0)
            ->whereDoesntHave('transfer')
            ->with([
                'register',
                'openedBy',
                'closedBy',
            ])
            ->latest('closed_at')
            ->first();
    }
}
