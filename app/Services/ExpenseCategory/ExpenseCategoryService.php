<?php

declare(strict_types=1);

namespace App\Services\ExpenseCategory;

use App\Models\ExpenseCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExpenseCategoryService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(ExpenseCategory::class)
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('isActive', 'is_active'),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): ExpenseCategory
    {
        return ExpenseCategory::query()->create([
            'name' => $data['name'],
            'is_active' => $data['isActive'] ?? true,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function show(ExpenseCategory $expenseCategory): ExpenseCategory
    {
        return $expenseCategory;
    }

    public function update(ExpenseCategory $expenseCategory, array $data): ExpenseCategory
    {
        $expenseCategory->update([
            'name' => $data['name'] ?? $expenseCategory->name,
            'is_active' => array_key_exists('isActive', $data)
                ? $data['isActive']
                : $expenseCategory->is_active,
            'notes' => array_key_exists('notes', $data)
                ? $data['notes']
                : $expenseCategory->notes,
        ]);

        return $expenseCategory->refresh();
    }

    public function delete(ExpenseCategory $expenseCategory): bool
    {
        return (bool) $expenseCategory->delete();
    }
}
