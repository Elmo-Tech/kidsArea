<?php

namespace App\Http\Resources\V1\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'status' => $this->status->value,

            'pricingPlansCount' =>
                $this->pricing_plans_count ?? 0,
        ];
    }
}
