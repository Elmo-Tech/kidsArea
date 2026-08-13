<?php

namespace App\Http\Resources\V1\EmployeeContract;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeContractCollection extends ResourceCollection
{
    public $collects = AllEmployeeContractResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employeeContracts' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
