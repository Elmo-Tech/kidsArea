<?php

namespace App\Http\Resources\V1\ActivityPricingPlan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllActivityPricingPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'activity' => [
                'id' => $this->activity->id,
                'name' => $this->activity->name,
            ],

            'name' => $this->name,

            'type' => $this->type->value,

            'price' => $this->price,

            'durationValue' =>
                $this->duration_value,

            'durationUnit' =>
                $this->duration_unit?->value,

            'sessionsCount' =>
                $this->sessions_count,

            'status' => $this->status->value,
        ];
    }
}
