<?php

namespace App\Http\Resources\V1\ActivityAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityAttendanceCollection extends ResourceCollection
{
    public $collects = AllActivityAttendanceResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activityAttendances' => $this->collection,

            'pagination' => [
                'perPage' =>
                    $this->resource->perPage(),

                'totalPages' =>
                    $this->resource->lastPage(),

                'currentPage' =>
                    $this->resource->currentPage(),
            ],
        ];
    }
}
