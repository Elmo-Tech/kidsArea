<?php

namespace App\Http\Resources\V1\ActivitySchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityScheduleCollection extends ResourceCollection
{
    public $collects = AllActivityScheduleResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activitySchedules' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
