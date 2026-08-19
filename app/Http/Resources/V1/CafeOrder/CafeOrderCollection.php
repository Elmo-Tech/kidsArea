<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CafeOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CafeOrderCollection extends ResourceCollection
{
    public $collects = AllCafeOrderResource::class;

    public function toArray(Request $request): array
    {
        return [
            'cafeOrders' =>
                $this->collection,

            'pagination' => [
                'perPage' =>
                    $this->resource->perPage(),

                'totalPages' =>
                    $this->resource->lastPage(),

                'currentPage' =>
                    $this->resource->currentPage(),
            ],
        ];
    }
}
