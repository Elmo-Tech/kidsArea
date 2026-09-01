<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CashTransaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CashTransactionCollection extends ResourceCollection
{
    public $collects = AllCashTransactionResource::class;

    public function toArray(Request $request): array
    {
        return [
            'cashTransactions' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
