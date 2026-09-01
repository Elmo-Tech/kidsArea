<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashRegister;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CashRegisterCollection extends ResourceCollection
{
    public $collects = AllCashRegisterResource::class;

    public function toArray(Request $request): array
    {
        return [
            'cashRegisters' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
