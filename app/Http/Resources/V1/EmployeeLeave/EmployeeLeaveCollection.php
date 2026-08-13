<?php

namespace App\Http\Resources\V1\EmployeeLeave;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeLeaveCollection extends ResourceCollection
{
    public $collects = AllEmployeeLeaveResource::class;

    public function toArray(Request $request): array
    {
        return [
            'employeeLeaves' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
