<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Refund;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RefundCollection extends ResourceCollection
{
    public $collects = RefundResource::class;

    public function toArray(Request $request): array
    {
        return [
            'refunds' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
                'total' => $this->resource->total(),
            ],
        ];
    }
}
