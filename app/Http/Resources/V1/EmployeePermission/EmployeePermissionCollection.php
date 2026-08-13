<?php

namespace App\Http\Resources\V1\EmployeePermission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeePermissionCollection extends ResourceCollection
{

    public $collects = AllEmployeePermissionResource::class;
    public function toArray(Request $request): array
    {
        return [
            'employeePermissions' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
