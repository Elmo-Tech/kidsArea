<?php

declare(strict_types=1);

namespace App\Services\Cash;

use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionSourceEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\CashTransaction;
use Carbon\Carbon;

class CashReportService
{
    public function report(array $filters): array
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();

        $registers = CashRegister::query()
            ->orderByDesc('is_main')
            ->orderBy('id')
            ->get();

        $registersData = $registers->map(function (CashRegister $register) use ($from, $to): array {
            $transactions = CashTransaction::query()
                ->where('cash_register_id', $register->id)
                ->whereBetween('transaction_at', [$from, $to])
                ->get();

            $totalIn = (float) $transactions
                ->where('type', CashTransactionTypeEnum::IN)
                ->sum('amount');

            $totalOut = (float) $transactions
                ->where('type', CashTransactionTypeEnum::OUT)
                ->sum('amount');

            $bySource = $transactions
                ->groupBy(fn ($transaction) => $transaction->source->value)
                ->map(function ($items): array {
                    $totalIn = (float) $items->where('type', CashTransactionTypeEnum::IN)->sum('amount');
                    $totalOut = (float) $items->where('type', CashTransactionTypeEnum::OUT)->sum('amount');

                    return [
                        'totalIn' => round($totalIn, 2),
                        'totalOut' => round($totalOut, 2),
                        'netMovement' => round($totalIn - $totalOut, 2),
                    ];
                });

            $emptySource = ['totalIn' => 0, 'totalOut' => 0, 'netMovement' => 0];

            $shifts = CashShift::query()
                ->where('cash_register_id', $register->id)
                ->whereBetween('closed_at', [$from, $to])
                ->where('status', CashShiftStatusEnum::CLOSED->value)
                ->get();

            $actualClosingBalance = (float) $shifts->sum('actual_closing_balance');
            $difference = (float) $shifts->sum('difference');

            return [
                'cashRegisterId' => $register->id,
                'cashRegisterName' => $register->name,
                'isMain' => $register->is_main,
                'transactionsCount' => $transactions->count(),
                'closedShiftsCount' => $shifts->count(),
                'totalIn' => round($totalIn, 2),
                'totalOut' => round($totalOut, 2),
                'netMovement' => round($totalIn - $totalOut, 2),
                'actualClosingBalance' => round($actualClosingBalance, 2),
                'difference' => round($difference, 2),
                'bySource' => [
                    'payment' => $bySource->get(CashTransactionSourceEnum::PAYMENT->value, $emptySource),
                    'payrollPayment' => $bySource->get(CashTransactionSourceEnum::PAYROLL_PAYMENT->value, $emptySource),
                    'expense' => $bySource->get(CashTransactionSourceEnum::EXPENSE->value, $emptySource),
                    'manual' => $bySource->get(CashTransactionSourceEnum::MANUAL->value, $emptySource),
                    'refund' => $bySource->get(CashTransactionSourceEnum::REFUND->value, $emptySource),
                    'transfer' => $bySource->get(CashTransactionSourceEnum::TRANSFER->value, $emptySource),
                ],
            ];
        });

        return [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'totalIn' => round((float) $registersData->sum('totalIn'), 2),
            'totalOut' => round((float) $registersData->sum('totalOut'), 2),
            'netMovement' => round(
                (float) $registersData->sum('totalIn')
                - (float) $registersData->sum('totalOut'),
                2
            ),
            'actualClosingBalance' => round(
                (float) $registersData->sum('actualClosingBalance'),
                2
            ),
            'difference' => round(
                (float) $registersData->sum('difference'),
                2
            ),
            'registers' => $registersData->values(),
        ];
    }
}
