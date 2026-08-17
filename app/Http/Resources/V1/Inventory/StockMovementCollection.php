<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StockMovementCollection extends ResourceCollection
{
    public $collects = AllStockMovementResource::class;

    public function toArray(Request $request): array
    {
        return [
            'stockMovements' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
