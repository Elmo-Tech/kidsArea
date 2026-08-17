<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllStockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'inventoryItem' => [
                'id' => $this->inventoryItem->id,
                'name' => $this->inventoryItem->name,
            ],

            'type' => $this->type->value,

            'quantity' => $this->quantity,

            'movementAt' =>
                $this->movement_at,

            'createdBy' => $this->createdBy
                ? [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ]
                : null,
        ];
    }
}
