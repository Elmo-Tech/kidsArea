<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\VisitPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VisitPaymentCollection extends ResourceCollection
{
    public $collects = AllVisitPaymentResource::class;

    public function toArray(Request $request): array
    {
        return [
            'visitPayments' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
