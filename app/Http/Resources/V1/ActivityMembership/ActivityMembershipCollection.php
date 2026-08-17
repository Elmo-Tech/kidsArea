<?php

namespace App\Http\Resources\V1\ActivityMembership;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityMembershipCollection extends ResourceCollection
{
    public $collects = AllActivityMembershipResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activityMemberships' => $this->collection,

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
