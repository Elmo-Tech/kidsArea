<?php

namespace App\Http\Resources\V1\LeaveType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class LeaveTypeCollection extends ResourceCollection
{
    public $collects = AllLeaveTypeResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
