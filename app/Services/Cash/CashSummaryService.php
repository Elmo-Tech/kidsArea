<?php

declare(strict_types=1);

namespace App\Services\Cash;

use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\CashTransfer;

class CashSummaryService
{
    public function general(): array
    {
        $registers = CashRegister::query()
            ->with(['openShift.openedBy'])
            ->orderByDesc('is_main')
            ->orderBy('id')
            ->get();

        $registerData = $registers->map(function (CashRegister $register): array {
            return $register->is_main
                ? $this->mainRegisterSummary($register)
                : $this->cashierRegisterSummary($register);
        });

        $mainRegisterBalance = round((float) $registerData->where('isMain', true)->sum('balance'), 2);
        $cashierRegistersBalance = round((float) $registerData->where('isMain', false)->sum('balance'), 2);

        return [
            'mainRegisterBalance' => $mainRegisterBalance,
            'cashierRegistersBalance' => $cashierRegistersBalance,
            'totalCash' => round($mainRegisterBalance + $cashierRegistersBalance, 2),
            'openRegistersCount' => $registerData->where('hasOpenShift', true)->count(),
            'pendingTransfersCount' => (int) $registerData->sum('pendingTransfersCount'),
            'pendingTransfersBalance' => round((float) $registerData->sum('pendingTransferBalance'), 2),
            'registers' => $registerData->values(),
        ];
    }

    private function mainRegisterSummary(CashRegister $register): array
    {
        $totalIn = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        $balance = round($totalIn - $totalOut, 2);

        return [
            'id' => $register->id,
            'name' => $register->name,
            'isMain' => true,
            'hasOpenShift' => false,
            'cashShiftId' => null,
            'openedBy' => null,
            'openingBalance' => 0,
            'totalIn' => round($totalIn, 2),
            'totalOut' => round($totalOut, 2),
            'balance' => $balance,
            'expectedBalance' => $balance,
            'actualClosingBalance' => null,
            'difference' => null,
            'status' => null,
            'pendingTransfersCount' => 0,
            'pendingTransferBalance' => 0,
        ];
    }

    private function cashierRegisterSummary(CashRegister $register): array
    {
        $shift = $register->openShift;
        $summary = $shift ? $this->shiftSummary($shift) : null;

        $pendingShifts = CashShift::query()
            ->where('cash_register_id', $register->id)
            ->where('status', CashShiftStatusEnum::CLOSED->value)
            ->whereNotNull('actual_closing_balance')
            ->where('actual_closing_balance', '>', 0)
            ->whereNotIn(
                'id',
                CashTransfer::query()->select('from_cash_shift_id')
            )
            ->orderBy('closed_at')
            ->get(['id', 'actual_closing_balance']);

        $pendingTransferBalance = round((float) $pendingShifts->sum('actual_closing_balance'), 2);
        $openShiftBalance = round((float) ($summary['expectedBalance'] ?? 0), 2);

        return [
            'id' => $register->id,
            'name' => $register->name,
            'isMain' => false,
            'hasOpenShift' => $shift !== null,
            'cashShiftId' => $shift?->id,
            'openedBy' => $shift?->openedBy
                ? ['id' => $shift->openedBy->id, 'name' => $shift->openedBy->name]
                : null,
            'openingBalance' => $summary['openingBalance'] ?? 0,
            'totalIn' => $summary['totalIn'] ?? 0,
            'totalOut' => $summary['totalOut'] ?? 0,
            'balance' => round($openShiftBalance + $pendingTransferBalance, 2),
            'expectedBalance' => $openShiftBalance,
            'actualClosingBalance' => $summary['actualClosingBalance'] ?? null,
            'difference' => $summary['difference'] ?? null,
            'status' => $summary['status'] ?? null,
            'pendingTransfersCount' => $pendingShifts->count(),
            'pendingTransferBalance' => $pendingTransferBalance,
        ];
    }

    public function shiftSummary(CashShift $shift): array
    {
        $totalIn = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        $expectedBalance = round((float) $shift->opening_balance + $totalIn - $totalOut, 2);

        return [
            'openingBalance' => round((float) $shift->opening_balance, 2),
            'totalIn' => round($totalIn, 2),
            'totalOut' => round($totalOut, 2),
            'expectedBalance' => $expectedBalance,
            'actualClosingBalance' => $shift->actual_closing_balance !== null
                ? round((float) $shift->actual_closing_balance, 2)
                : null,
            'difference' => $shift->difference !== null
                ? round((float) $shift->difference, 2)
                : null,
            'status' => $shift->status->value,
        ];
    }
}
