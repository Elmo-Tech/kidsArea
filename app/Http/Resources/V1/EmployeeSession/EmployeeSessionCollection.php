<?php

namespace App\Http\Resources\V1\EmployeeSession;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeSessionCollection extends ResourceCollection
{
    public $collects = AllEmployeeSessionResource::class;

    public function toArray(Request $request): array
    {
        return [
            'employeeSessions' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
