<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'inventoryItem' => [
                'id' => $this->inventoryItem->id,
                'name' => $this->inventoryItem->name,
                'baseUnit' => $this->inventoryItem->base_unit->value,
            ],

            'type' => $this->type->value,

            'quantity' => $this->quantity,

            'unitCost' => $this->unit_cost,

            'totalCost' => $this->total_cost,

            'sourceType' => $this->source_type,

            'sourceId' => $this->source_id,

            'movementAt' =>
                $this->movement_at,
            'notes' => $this->notes,

            'createdBy' => $this->createdBy
                ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ]
                : null,

            'createdAt' =>
                $this->created_at
        ];
    }
}
