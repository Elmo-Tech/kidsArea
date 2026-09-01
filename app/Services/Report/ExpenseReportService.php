<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ExpenseReportService
{
    public function report(array $data): array
    {
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        $expenseCategoryId = isset($data['expenseCategoryId'])
            ? (int) $data['expenseCategoryId']
            : null;

        $cashRegisterId = isset($data['cashRegisterId'])
            ? (int) $data['cashRegisterId']
            : null;

        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],

            'filters' => [
                'expenseCategoryId' => $expenseCategoryId,
                'cashRegisterId' => $cashRegisterId,
            ],

            'summary' => $this->summary(
                $from,
                $to,
                $expenseCategoryId,
                $cashRegisterId
            ),

            'byCategory' => $this->byCategory(
                $from,
                $to,
                $expenseCategoryId,
                $cashRegisterId
            ),

            'byRegister' => $this->byRegister(
                $from,
                $to,
                $expenseCategoryId,
                $cashRegisterId
            ),

            'daily' => $this->daily(
                $from,
                $to,
                $expenseCategoryId,
                $cashRegisterId
            ),
        ];
    }

    private function summary(
        Carbon $from,
        Carbon $to,
        ?int $expenseCategoryId,
        ?int $cashRegisterId
    ): array {
        $row = $this->baseQuery(
            $from,
            $to,
            $expenseCategoryId,
            $cashRegisterId
        )
            ->selectRaw(
                'COUNT(*) as expenses_count,
                 COALESCE(SUM(amount), 0) as total_amount,
                 COALESCE(AVG(amount), 0) as average_amount,
                 COALESCE(MAX(amount), 0) as highest_amount'
            )
            ->first();

        return [
            'expensesCount' => (int) ($row->expenses_count ?? 0),
            'total' => round((float) ($row->total_amount ?? 0), 2),
            'average' => round((float) ($row->average_amount ?? 0), 2),
            'highest' => round((float) ($row->highest_amount ?? 0), 2),
        ];
    }

    private function byCategory(
        Carbon $from,
        Carbon $to,
        ?int $expenseCategoryId,
        ?int $cashRegisterId
    ): array {
        return $this->baseQuery(
            $from,
            $to,
            $expenseCategoryId,
            $cashRegisterId
        )
            ->join(
                'expense_categories',
                'expense_categories.id',
                '=',
                'expenses.expense_category_id'
            )
            ->selectRaw(
                'expense_categories.id,
                 expense_categories.name,
                 COUNT(expenses.id) as expenses_count,
                 COALESCE(SUM(expenses.amount), 0) as total_amount'
            )
            ->groupBy(
                'expense_categories.id',
                'expense_categories.name'
            )
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($row): array => [
                'categoryId' => (int) $row->id,
                'categoryName' => $row->name,
                'expensesCount' => (int) $row->expenses_count,
                'total' => round((float) $row->total_amount, 2),
            ])
            ->values()
            ->all();
    }

    private function byRegister(
        Carbon $from,
        Carbon $to,
        ?int $expenseCategoryId,
        ?int $cashRegisterId
    ): array {
        return $this->baseQuery(
            $from,
            $to,
            $expenseCategoryId,
            $cashRegisterId
        )
            ->join(
                'cash_registers',
                'cash_registers.id',
                '=',
                'expenses.cash_register_id'
            )
            ->selectRaw(
                'cash_registers.id,
                 cash_registers.name,
                 cash_registers.is_main,
                 COUNT(expenses.id) as expenses_count,
                 COALESCE(SUM(expenses.amount), 0) as total_amount'
            )
            ->groupBy(
                'cash_registers.id',
                'cash_registers.name',
                'cash_registers.is_main'
            )
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($row): array => [
                'cashRegisterId' => (int) $row->id,
                'cashRegisterName' => $row->name,
                'isMain' => (bool) $row->is_main,
                'expensesCount' => (int) $row->expenses_count,
                'total' => round((float) $row->total_amount, 2),
            ])
            ->values()
            ->all();
    }

    private function daily(
        Carbon $from,
        Carbon $to,
        ?int $expenseCategoryId,
        ?int $cashRegisterId
    ): array {
        return $this->baseQuery(
            $from,
            $to,
            $expenseCategoryId,
            $cashRegisterId
        )
            ->selectRaw(
                'DATE(expense_at) as expense_date,
                 COUNT(*) as expenses_count,
                 COALESCE(SUM(amount), 0) as total_amount'
            )
            ->groupByRaw('DATE(expense_at)')
            ->orderBy('expense_date')
            ->get()
            ->map(fn ($row): array => [
                'date' => $row->expense_date,
                'expensesCount' => (int) $row->expenses_count,
                'total' => round((float) $row->total_amount, 2),
            ])
            ->values()
            ->all();
    }

    private function baseQuery(
        Carbon $from,
        Carbon $to,
        ?int $expenseCategoryId,
        ?int $cashRegisterId
    ): Builder {
        return Expense::query()
            ->whereBetween('expense_at', [$from, $to])
            ->when(
                $expenseCategoryId !== null,
                fn (Builder $query): Builder =>
                    $query->where('expense_category_id', $expenseCategoryId)
            )
            ->when(
                $cashRegisterId !== null,
                fn (Builder $query): Builder =>
                    $query->where('cash_register_id', $cashRegisterId)
            );
    }
}
