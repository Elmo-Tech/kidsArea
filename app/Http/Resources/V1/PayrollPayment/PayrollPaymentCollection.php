<?php

namespace App\Http\Resources\V1\PayrollPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PayrollPaymentCollection extends ResourceCollection
{
    public $collects = AllPayrollPaymentResource::class;

    public function toArray(Request $request): array
    {
        return [
            'payrollPayments' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
