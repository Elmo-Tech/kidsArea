<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\VisitCheckout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VisitCheckoutCollection extends ResourceCollection
{
    public $collects = AllVisitCheckoutResource::class;

    public function toArray(Request $request): array
    {
        return [
            'VisitCheckouts' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
