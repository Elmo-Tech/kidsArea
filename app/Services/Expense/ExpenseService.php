<?php

declare(strict_types=1);

namespace App\Services\Expense;

use App\Enums\CashShiftStatusEnum;
use App\Enums\CashTransactionSourceEnum;
use App\Enums\CashTransactionTypeEnum;
use App\Exceptions\Cash\CashShiftNotOpenException;
use App\Exceptions\Cash\CashShiftNotOwnedByCurrentUserException;
use App\Exceptions\Expense\ExpenseCashShiftRequiredException;
use App\Exceptions\Expense\ExpenseInsufficientCashException;
use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\CashTransaction;
use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExpenseService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(Expense::class)
            ->with(['register', 'shift', 'createdBy'])
            ->allowedFilters([
                AllowedFilter::exact('cashRegisterId', 'cash_register_id'),
                AllowedFilter::exact('cashShiftId', 'cash_shift_id'),
                AllowedFilter::callback('from', function ($query, $value): void {
                    $query->whereDate('expense_at', '>=', $value);
                }),
                AllowedFilter::callback('to', function ($query, $value): void {
                    $query->whereDate('expense_at', '<=', $value);
                }),
                AllowedFilter::callback('search', function ($query, $value): void {
                    $query->where('title', 'like', '%' . $value . '%');
                }),
            ])
            ->latest('expense_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): Expense
    {
        return DB::transaction(function () use ($data): Expense {
            $register = CashRegister::query()
                ->whereKey($data['cashRegisterId'])
                ->lockForUpdate()
                ->firstOrFail();

            $amount = round((float) $data['amount'], 2);

            $shift = $register->is_main
                ? null
                : $this->resolveShift($register, $data);

            $availableBalance = $register->is_main
                ? $this->calculateMainRegisterBalance($register)
                : $this->calculateShiftBalance($shift);

            if ($amount > $availableBalance) {
                throw new ExpenseInsufficientCashException();
            }

            $expense = Expense::query()->create([
                'cash_register_id' => $register->id,
                'cash_shift_id' => $shift?->id,
                'title' => $data['title'],
                'amount' => $amount,
                'expense_at' => $data['expenseAt'] ?? now(),
                'expense_category_id' => $data['expenseCategoryId'],
                'notes' => $data['notes'] ?? null,
            ]);

            CashTransaction::query()->create([
                'cash_register_id' => $register->id,
                'cash_shift_id' => $shift?->id,
                'type' => CashTransactionTypeEnum::OUT->value,
                'amount' => $amount,
                'source' => CashTransactionSourceEnum::EXPENSE->value,
                'sourceable_type' => Expense::class,
                'sourceable_id' => $expense->id,
                'transaction_at' => $expense->expense_at,
                'notes' => $expense->title,
            ]);

            return $this->show($expense);
        });
    }

    public function show(Expense $expense): Expense
    {
        return $expense->load([
            'register',
            'shift',
            'cashTransaction',
            'createdBy',
            'category',
        ]);
    }

    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data): Expense {
            $expense = Expense::query()
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureExistingExpenseIsEditable($expense);

            $registerId = array_key_exists('cashRegisterId', $data)
                ? (int) $data['cashRegisterId']
                : (int) $expense->cash_register_id;

            $register = CashRegister::query()
                ->whereKey($registerId)
                ->lockForUpdate()
                ->firstOrFail();

            $shift = null;

            if (! $register->is_main) {
                $registerChanged = (int) $expense->cash_register_id !== (int) $register->id;

                if ($registerChanged && empty($data['cashShiftId'])) {
                    throw new ExpenseCashShiftRequiredException();
                }

                $shiftId = array_key_exists('cashShiftId', $data)
                    ? $data['cashShiftId']
                    : $expense->cash_shift_id;

                if (! $shiftId) {
                    throw new ExpenseCashShiftRequiredException();
                }

                $shift = $this->resolveShiftById($register, (int) $shiftId);
            }

            $amount = array_key_exists('amount', $data)
                ? round((float) $data['amount'], 2)
                : round((float) $expense->amount, 2);

            $transaction = CashTransaction::query()
                ->where('sourceable_type', Expense::class)
                ->where('sourceable_id', $expense->id)
                ->where('source', CashTransactionSourceEnum::EXPENSE->value)
                ->lockForUpdate()
                ->firstOrFail();

            $availableBalance = $register->is_main
                ? $this->calculateMainRegisterBalanceIgnoringExpense($register, $transaction)
                : $this->calculateShiftBalanceIgnoringExpense($shift, $transaction);

            if ($amount > $availableBalance) {
                throw new ExpenseInsufficientCashException();
            }

            $expense->update([
                'cash_register_id' => $register->id,
                'cash_shift_id' => $shift?->id,
                'title' => array_key_exists('title', $data) ? $data['title'] : $expense->title,
                'amount' => $amount,
                'expense_category_id' => array_key_exists('expenseCategoryId', $data)
                    ? $data['expenseCategoryId']
                    : $expense->expense_category_id,
                'expense_at' => array_key_exists('expenseAt', $data)
                    ? ($data['expenseAt'] ?? now())
                    : $expense->expense_at,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $expense->notes,
            ]);

            $transaction->update([
                'cash_register_id' => $register->id,
                'cash_shift_id' => $shift?->id,
                'type' => CashTransactionTypeEnum::OUT->value,
                'amount' => $amount,
                'transaction_at' => $expense->expense_at,
                'notes' => $expense->title,
            ]);

            return $this->show($expense->refresh());
        });
    }

    private function resolveShift(CashRegister $register, array $data): CashShift
    {
        if (empty($data['cashShiftId'])) {
            throw new ExpenseCashShiftRequiredException();
        }

        return $this->resolveShiftById($register, (int) $data['cashShiftId']);
    }

    private function resolveShiftById(CashRegister $register, int $shiftId): CashShift
    {
        $shift = CashShift::query()
            ->whereKey($shiftId)
            ->where('cash_register_id', $register->id)
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureShiftCanBeUsed($shift);

        return $shift;
    }

    private function ensureExistingExpenseIsEditable(Expense $expense): void
    {
        if ($expense->cash_shift_id === null) {
            return;
        }

        $shift = CashShift::query()
            ->whereKey($expense->cash_shift_id)
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureShiftCanBeUsed($shift);
    }

    private function ensureShiftCanBeUsed(CashShift $shift): void
    {
        if ($shift->status !== CashShiftStatusEnum::OPEN) {
            throw new CashShiftNotOpenException();
        }

        $user = Auth::user();

        if ((int) $shift->opened_by !== (int) $user?->id && ! $user?->can('cash-shifts.manage-all')) {
            throw new CashShiftNotOwnedByCurrentUserException();
        }
    }

    private function calculateShiftBalance(CashShift $shift): float
    {
        $totalIn = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        return round((float) $shift->opening_balance + $totalIn - $totalOut, 2);
    }

    private function calculateMainRegisterBalance(CashRegister $register): float
    {
        $totalIn = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->sum('amount');

        return round($totalIn - $totalOut, 2);
    }

    private function calculateMainRegisterBalanceIgnoringExpense(
        CashRegister $register,
        CashTransaction $expenseTransaction
    ): float {
        $totalIn = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) CashTransaction::query()
            ->where('cash_register_id', $register->id)
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->where('id', '!=', $expenseTransaction->id)
            ->sum('amount');

        return round($totalIn - $totalOut, 2);
    }

    private function calculateShiftBalanceIgnoringExpense(
        CashShift $shift,
        CashTransaction $expenseTransaction
    ): float {
        $totalIn = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::IN->value)
            ->sum('amount');

        $totalOut = (float) $shift->transactions()
            ->where('type', CashTransactionTypeEnum::OUT->value)
            ->where('id', '!=', $expenseTransaction->id)
            ->sum('amount');

        return round((float) $shift->opening_balance + $totalIn - $totalOut, 2);
    }
}
