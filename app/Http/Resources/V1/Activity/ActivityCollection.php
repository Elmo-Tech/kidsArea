<?php

namespace App\Http\Resources\V1\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityCollection extends ResourceCollection
{
    public $collects = AllActivityResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activities' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
