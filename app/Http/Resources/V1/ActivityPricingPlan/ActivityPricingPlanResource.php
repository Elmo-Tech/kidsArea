<?php

namespace App\Http\Resources\V1\ActivityPricingPlan;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityPricingPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'activity' => $this->whenLoaded(
                'activity',
                fn (): array => [
                    'id' => $this->activity->id,
                    'name' => $this->activity->name,
                ]
            ),

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

            'notes' => $this->notes,

            'createdAt' =>
                $this->created_at?->format('Y-m-d H:i:s'),

            'updatedAt' =>
                $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
