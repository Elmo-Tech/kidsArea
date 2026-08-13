<?php

namespace App\Http\Resources\V1\ActivityPricingPlan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityPricingPlanCollection extends ResourceCollection
{
    public $collects = AllActivityPricingPlanResource::class;

    public function toArray(Request $request): array
    {
        return [
            'activityPricingPlans' => $this->collection,

            'pagination' => [
                'perPage' => $this->resource->perPage(),
                'totalPages' => $this->resource->lastPage(),
                'currentPage' => $this->resource->currentPage(),
            ],
        ];
    }
}
