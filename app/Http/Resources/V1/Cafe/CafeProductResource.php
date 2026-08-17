<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Cafe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CafeProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'description' => $this->description,

            'sellingPrice' => $this->selling_price,

            'isActive' => $this->is_active,

            'ingredients' => $this->whenLoaded(
                'ingredients',
                fn () => $this->ingredients->map(
                    fn ($ingredient) => [
                        'id' => $ingredient->id,

                        'inventoryItem' => [
                            'id' => $ingredient->inventoryItem->id,
                            'name' => $ingredient->inventoryItem->name,
                            'baseUnit' => $ingredient
                                ->inventoryItem
                                ->base_unit
                                ->value,
                        ],

                        'quantity' =>
                            $ingredient->quantity,

                        'unitCost' =>
                            $ingredient
                                ->inventoryItem
                                ->unit_cost,

                        'ingredientCost' =>
                            number_format(
                                (float) $ingredient->quantity
                                * (float) (
                                    $ingredient
                                        ->inventoryItem
                                        ->unit_cost
                                    ?? 0
                                ),
                                4,
                                '.',
                                ''
                            ),
                    ]
                )
            ),

            'recipeCost' =>
                $this->whenLoaded(
                    'ingredients',
                    fn () => number_format(
                        $this->ingredients->sum(
                            fn ($ingredient) =>
                                (float) $ingredient->quantity
                                * (float) (
                                    $ingredient
                                        ->inventoryItem
                                        ->unit_cost
                                    ?? 0
                                )
                        ),
                        4,
                        '.',
                        ''
                    )
                ),

            'notes' => $this->notes,

            'createdAt' =>
                $this->created_at
        ];
    }
}
