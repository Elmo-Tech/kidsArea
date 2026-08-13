<?php

namespace App\Http\Resources\V1\EmployeeAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeAttendanceCollection extends ResourceCollection
{
    public $collects = AllEmployeeAttendanceResource::class;

    public function toArray(Request $request): array
    {
        return [
            'employeeAttendances' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
