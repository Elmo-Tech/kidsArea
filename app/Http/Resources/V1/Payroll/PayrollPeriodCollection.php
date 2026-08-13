<?php

namespace App\Http\Resources\V1\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PayrollPeriodCollection extends ResourceCollection
{
    public $collects = AllPayrollPeriodResource::class;

    public function toArray(Request $request): array
    {
        return [
            'payrollPeriods' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
