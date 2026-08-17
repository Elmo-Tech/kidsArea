<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\QueryFilters\InventoryItemSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryItemService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(InventoryItem::class)
            ->allowedFilters(
                AllowedFilter::custom(
                    'search',
                    new InventoryItemSearchFilter()
                ),

                AllowedFilter::exact(
                    'baseUnit',
                    'base_unit'
                ),

                AllowedFilter::exact(
                    'isActive',
                    'is_active'
                ),

                AllowedFilter::callback(
                    'lowStock',
                    function ($query, $value): void {
                        if (! filter_var(
                            $value,
                            FILTER_VALIDATE_BOOLEAN
                        )) {
                            return;
                        }

                        $query
                            ->whereNotNull('minimum_quantity')
                            ->whereColumn(
                                'current_quantity',
                                '<=',
                                'minimum_quantity'
                            );
                    }
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createInventoryItem(
        array $data
    ): InventoryItem {
        return DB::transaction(function () use ($data): InventoryItem {
            return InventoryItem::create([
                'name' =>
                    $data['name'],

                'base_unit' =>
                    $data['baseUnit'],

                /*
                 * Stock must only change through
                 * stock movements.
                 */
                'current_quantity' =>
                    0,

                'minimum_quantity' =>
                    $data['minimumQuantity'] ?? null,

                'unit_cost' =>
                    $data['unitCost'] ?? null,

                'is_active' =>
                    $data['isActive'] ?? true,

                'notes' =>
                    $data['notes'] ?? null,
            ]);
        });
    }

    public function editInventoryItem(
        InventoryItem $inventoryItem
    ): InventoryItem {
        return $inventoryItem;
    }

    public function updateInventoryItem(
        InventoryItem $inventoryItem,
        array $data
    ): InventoryItem {
        return DB::transaction(function () use (
            $inventoryItem,
            $data
        ): InventoryItem {
            $inventoryItem->update([
                'name' =>
                    $data['name']
                    ?? $inventoryItem->name,

                'base_unit' =>
                    $data['baseUnit']
                    ?? $inventoryItem->base_unit->value,

                'minimum_quantity' =>
                    array_key_exists(
                        'minimumQuantity',
                        $data
                    )
                        ? $data['minimumQuantity']
                        : $inventoryItem->minimum_quantity,

                'unit_cost' =>
                    array_key_exists(
                        'unitCost',
                        $data
                    )
                        ? $data['unitCost']
                        : $inventoryItem->unit_cost,

                'is_active' =>
                    $data['isActive']
                    ?? $inventoryItem->is_active,

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $inventoryItem->notes,
            ]);

            return $inventoryItem->refresh();
        });
    }

    public function deleteInventoryItem(
        InventoryItem $inventoryItem
    ): bool {
        return DB::transaction(
            fn (): bool =>
                (bool) $inventoryItem->delete()
        );
    }
}
