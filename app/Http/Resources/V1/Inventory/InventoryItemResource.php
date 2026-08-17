<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'baseUnit' => $this->base_unit->value,

            'currentQuantity' =>
                $this->current_quantity,

            'minimumQuantity' =>
                $this->minimum_quantity,

            'unitCost' =>
                $this->unit_cost,

            'isActive' =>
                $this->is_active,

            'isLowStock' =>
                $this->minimum_quantity !== null
                && (float) $this->current_quantity
                    <= (float) $this->minimum_quantity,

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at
        ];
    }
}
