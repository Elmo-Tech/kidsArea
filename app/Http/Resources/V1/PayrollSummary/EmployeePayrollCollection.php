<?php

namespace App\Http\Resources\V1\EmployeePayroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeePayrollCollection extends ResourceCollection
{
    public $collects = AllEmployeePayrollResource::class;

    public function toArray(Request $request): array
    {
        return [
            'employeePayrolls' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
