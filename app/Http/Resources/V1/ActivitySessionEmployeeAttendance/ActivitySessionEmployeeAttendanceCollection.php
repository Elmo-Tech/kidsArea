<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\ActivitySessionEmployeeAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivitySessionEmployeeAttendanceCollection extends ResourceCollection
{
    public $collects = AllActivitySessionEmployeeAttendanceResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activitySessionEmployeeAttendances' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
