<?php

declare(strict_types=1);

namespace App\Services\Cash;

use App\Enums\CashRegisterStatusEnum;
use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Exceptions\Cash\CashRegisterAlreadyHasOpenShiftException;
use App\Exceptions\Cash\CashRegisterInactiveException;
use App\Exceptions\Cash\CashShiftAlreadyClosedException;
use App\Models\CashRegister;
use App\Models\CashShift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Exceptions\Cash\CashShiftNotOwnedByCurrentUserException;
use App\Exceptions\Cash\CashRegisterHasPendingTransferException;
use App\Models\CashTransfer;

class CashShiftService
{

    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(CashShift::class)
            ->with([
                'register',
                'openedBy',
                'closedBy',
            ])
            ->allowedFilters([
                AllowedFilter::exact(
                    'cashRegisterId',
                    'cash_register_id'
                ),

                AllowedFilter::exact(
                    'openedBy',
                    'opened_by'
                ),

                AllowedFilter::exact(
                    'status'
                ),

                AllowedFilter::callback(
                    'from',
                    function ($query, $value): void {
                        $query->whereDate(
                            'opened_at',
                            '>=',
                            $value
                        );
                    }
                ),

                AllowedFilter::callback(
                    'to',
                    function ($query, $value): void {
                        $query->whereDate(
                            'opened_at',
                            '<=',
                            $value
                        );
                    }
                ),
            ])
            ->latest('opened_at')
            ->paginate($perPage);
    }
    public function open(array $data): CashShift
    {
        return DB::transaction(function () use ($data): CashShift {
            $register = CashRegister::query()
                ->whereKey($data['cashRegisterId'])
                ->where('is_main', false)
                ->lockForUpdate()
                ->firstOrFail();

            if ($register->status !== CashRegisterStatusEnum::ACTIVE) {
                throw new CashRegisterInactiveException();
            }

            $hasOpenShift = $register->shifts()
                ->where('status', CashShiftStatusEnum::OPEN->value)
                ->exists();

            if ($hasOpenShift) {
                throw new CashRegisterAlreadyHasOpenShiftException();
            }

            $hasPendingTransfer = CashShift::query()
                ->where('opened_by', Auth::id())
                ->where('status', CashShiftStatusEnum::CLOSED->value)
                ->whereNotNull('actual_closing_balance')
                ->where('actual_closing_balance', '>', 0)
                ->whereNotIn(
                    'id',
                    CashTransfer::query()->select('from_cash_shift_id')
                )
                ->exists();

            if ($hasPendingTransfer) {
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

    public function close(CashShift $cashShift, array $data): CashShift
    {
        return DB::transaction(function () use ($cashShift, $data): CashShift {
            $cashShift = CashShift::query()
                ->whereKey($cashShift->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cashShift->status === CashShiftStatusEnum::CLOSED) {
                throw new CashShiftAlreadyClosedException();
            }

            $this->ensureUserCanManageShift($cashShift);

            $expectedClosingBalance = $this->calculateExpectedBalance($cashShift);
            $actualClosingBalance = round((float) $data['actualClosingBalance'], 2);
            $difference = round($actualClosingBalance - $expectedClosingBalance, 2);

            $cashShift->update([
                'closed_by' => Auth::id(),
                'closed_at' => now(),
                'expected_closing_balance' => $expectedClosingBalance,
                'actual_closing_balance' => $actualClosingBalance,
                'difference' => $difference,
                'status' => CashShiftStatusEnum::CLOSED->value,
                'notes' => array_key_exists('notes', $data)
                    ? $data['notes']
                    : $cashShift->notes,
            ]);

            return $this->loadShift($cashShift->refresh());
        });
    }
    public function show(CashShift $cashShift): CashShift
    {
        return $this->loadShift($cashShift);
    }

    public function summary(CashShift $cashShift): array
    {
        $totalIn = (float) $cashShift->transactions()
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) $cashShift->transactions()
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        $expectedBalance = round(
            (float) $cashShift->opening_balance + $totalIn - $totalOut,
            2
        );

        return [
            'openingBalance' => round((float) $cashShift->opening_balance, 2),
            'totalIn' => round($totalIn, 2),
            'totalOut' => round($totalOut, 2),
            'expectedBalance' => $expectedBalance,
            'actualClosingBalance' => $cashShift->actual_closing_balance !== null
                ? round((float) $cashShift->actual_closing_balance, 2)
                : null,
            'difference' => $cashShift->difference !== null
                ? round((float) $cashShift->difference, 2)
                : null,
        ];
    }

    private function calculateExpectedBalance(CashShift $cashShift): float
    {
        $totalIn = (float) $cashShift->transactions()
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) $cashShift->transactions()
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        return round(
            (float) $cashShift->opening_balance + $totalIn - $totalOut,
            2
        );
    }

    private function loadShift(CashShift $cashShift): CashShift
    {
        return $cashShift->load([
            'register',
            'openedBy',
            'closedBy',
        ]);
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
}
