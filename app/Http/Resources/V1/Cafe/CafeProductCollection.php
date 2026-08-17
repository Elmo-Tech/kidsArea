<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Cafe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CafeProductCollection extends ResourceCollection
{
    public $collects = AllCafeProductResource::class;

    public function toArray(Request $request): array
    {
        return [
            'cafeProducts' => $this->collection,

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
