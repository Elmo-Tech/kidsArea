<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashShift;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CashShiftCollection extends ResourceCollection
{
    public $collects = CashShiftResource::class;

    public function toArray(Request $request): array
    {
        return [
            'cashInShifts' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
