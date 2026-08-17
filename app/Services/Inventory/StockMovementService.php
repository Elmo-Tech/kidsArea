<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Enums\StockMovementTypeEnum;
use App\Exceptions\Inventory\InsufficientStockException;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StockMovementService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(StockMovement::class)
            ->with([
                'inventoryItem',
                'createdBy',
            ])
            ->allowedFilters(
                AllowedFilter::exact(
                    'inventoryItemId',
                    'inventory_item_id'
                ),

                AllowedFilter::exact(
                    'type'
                ),

                AllowedFilter::exact(
                    'sourceType',
                    'source_type'
                ),
            )
            ->latest('movement_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function createMovement(
        array $data,
        ?string $sourceType = 'manual',
        ?int $sourceId = null
    ): StockMovement {
        return DB::transaction(function () use (
            $data,
            $sourceType,
            $sourceId
        ): StockMovement {

            $inventoryItem = InventoryItem::query()
                ->whereKey($data['inventoryItemId'])
                ->lockForUpdate()
                ->firstOrFail();

            $type = StockMovementTypeEnum::from(
                (int) $data['type']
            );

            $quantity = round(
                (float) $data['quantity'],
                3
            );

            $this->ensureStockIsEnough(
                $inventoryItem,
                $type,
                $quantity
            );

            $unitCost = array_key_exists('unitCost', $data)
                ? (float) $data['unitCost']
                : null;

            $totalCost = $unitCost !== null
                ? round($quantity * $unitCost, 4)
                : null;

            $movement = StockMovement::create([
                'inventory_item_id' =>
                    $inventoryItem->id,

                'type' =>
                    $type->value,

                'quantity' =>
                    $quantity,

                'unit_cost' =>
                    $unitCost,

                'total_cost' =>
                    $totalCost,

                'source_type' =>
                    $sourceType,

                'source_id' =>
                    $sourceId,

                'movement_at' =>
                    $data['movementAt'] ?? now(),

                'notes' =>
                    $data['notes'] ?? null,

                'created_by' =>
                    Auth::id(),
            ]);

            $this->applyMovementToStock(
                $inventoryItem,
                $type,
                $quantity
            );

            $this->updateInventoryItemCost(
                $inventoryItem,
                $type,
                $unitCost
            );

            return $movement->load([
                'inventoryItem',
                'createdBy',
            ]);
        });
    }

    public function showMovement(
        StockMovement $movement
    ): StockMovement {
        return $movement->load([
            'inventoryItem',
            'createdBy',
        ]);
    }

    private function ensureStockIsEnough(
        InventoryItem $inventoryItem,
        StockMovementTypeEnum $type,
        float $quantity
    ): void {
        if (
            ! $this->isStockOutMovement($type)
        ) {
            return;
        }

        if (
            (float) $inventoryItem->current_quantity
            < $quantity
        ) {
            throw new InsufficientStockException();
        }
    }

    private function applyMovementToStock(
        InventoryItem $inventoryItem,
        StockMovementTypeEnum $type,
        float $quantity
    ): void {
        $currentQuantity =
            (float) $inventoryItem->current_quantity;

        if ($this->isStockInMovement($type)) {
            $newQuantity =
                $currentQuantity + $quantity;
        } else {
            $newQuantity =
                $currentQuantity - $quantity;
        }

        $inventoryItem->update([
            'current_quantity' =>
                round($newQuantity, 3),
        ]);
    }

    private function updateInventoryItemCost(
        InventoryItem $inventoryItem,
        StockMovementTypeEnum $type,
        ?float $unitCost
    ): void {
        if ($unitCost === null) {
            return;
        }

        if (
            $type !==
            StockMovementTypeEnum::STOCK_IN
        ) {
            return;
        }

        /*
         * مبدئيًا نخزن آخر سعر شراء.
         * لو احتجنا Average Cost لاحقًا
         * نغير الحساب هنا فقط.
         */
        $inventoryItem->update([
            'unit_cost' =>
                round($unitCost, 4),
        ]);
    }

    private function isStockInMovement(
        StockMovementTypeEnum $type
    ): bool {
        return in_array(
            $type,
            [
                StockMovementTypeEnum::STOCK_IN,
                StockMovementTypeEnum::ADJUSTMENT_IN,
                StockMovementTypeEnum::RETURN,
            ],
            true
        );
    }

    private function isStockOutMovement(
        StockMovementTypeEnum $type
    ): bool {
        return in_array(
            $type,
            [
                StockMovementTypeEnum::CONSUMPTION,
                StockMovementTypeEnum::ADJUSTMENT_OUT,
                StockMovementTypeEnum::WASTE,
            ],
            true
        );
    }
}
