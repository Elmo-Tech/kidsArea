<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Enums\StockMovementTypeEnum;
use App\Exceptions\Inventory\InsufficientStockException;
use App\Models\CafeProduct;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class StockConsumptionService
{
    public function __construct(
        private readonly StockMovementService $stockMovementService
    ) {
    }

    public function consumeProduct(
        CafeProduct $product,
        int $quantity,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): void {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use (
            $product,
            $quantity,
            $sourceType,
            $sourceId
        ): void {

            $product->loadMissing([
                'ingredients.inventoryItem',
            ]);

            /*
             * First pass:
             * نتأكد إن كل الخامات متاحة
             * قبل ما نبدأ أي خصم.
             */
            foreach ($product->ingredients as $ingredient) {
                $requiredQuantity = round(
                    (float) $ingredient->quantity
                    * $quantity,
                    3
                );

                $inventoryItem = InventoryItem::query()
                    ->whereKey(
                        $ingredient->inventory_item_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    (float) $inventoryItem->current_quantity
                    < $requiredQuantity
                ) {
                    throw new InsufficientStockException();
                }
            }

            /*
             * Second pass:
             * الخصم الفعلي كله يمر من StockMovementService.
             */
            foreach ($product->ingredients as $ingredient) {
                $requiredQuantity = round(
                    (float) $ingredient->quantity
                    * $quantity,
                    3
                );

                $inventoryItem = InventoryItem::query()
                    ->findOrFail(
                        $ingredient->inventory_item_id
                    );

                $this->stockMovementService
                    ->createSystemMovement(
                        inventoryItem: $inventoryItem,
                        type: StockMovementTypeEnum::CONSUMPTION,
                        quantity: $requiredQuantity,
                        unitCost: $inventoryItem->unit_cost !== null
                            ? (float) $inventoryItem->unit_cost
                            : null,
                        sourceType: $sourceType,
                        sourceId: $sourceId,
                        notes:
                            "Consumed for cafe product: {$product->name}"
                    );
            }
        });
    }
}
