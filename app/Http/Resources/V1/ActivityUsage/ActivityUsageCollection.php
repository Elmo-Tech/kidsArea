<?php

namespace App\Http\Resources\V1\ActivityUsage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityUsageCollection extends ResourceCollection
{
    public $collects = AllActivityUsageResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activityUsages' => $this->collection,

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
