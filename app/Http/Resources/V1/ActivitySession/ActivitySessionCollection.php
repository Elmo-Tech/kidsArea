<?php

namespace App\Http\Resources\V1\ActivitySession;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivitySessionCollection extends ResourceCollection
{
    public $collects = AllActivitySessionResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activitySessions' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
