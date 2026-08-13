<?php

namespace App\Http\Resources\V1\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'description' => $this->description,

            'status' => $this->status->value,

            'notes' => $this->notes,

            'pricingPlans' =>
                ActivityPricingPlanResource::collection(
                    $this->whenLoaded('pricingPlans')
                ),

            'createdAt' =>
                $this->created_at?->format('Y-m-d H:i:s'),

            'updatedAt' =>
                $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
