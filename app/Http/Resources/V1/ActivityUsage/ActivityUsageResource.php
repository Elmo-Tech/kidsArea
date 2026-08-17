<?php

namespace App\Http\Resources\V1\ActivityUsage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityUsageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'visit' => $this->visit
                ? [
                    'id' => $this->visit->id,
                    'status' => $this->visit->status->value,
                ]
                : null,

            'child' => [
                'id' => $this->child->id,
                'name' => $this->child->name,
            ],

            'activity' => [
                'id' => $this->activity->id,
                'name' => $this->activity->name,
            ],

            'pricingPlan' => [
                'id' => $this->pricingPlan->id,
                'name' => $this->pricingPlan->name,
                'type' => $this->pricingPlan->type->value,
            ],

            'usageType' =>
                $this->usage_type->value,

            'startedAt' =>
                $this->started_at,

            'endedAt' =>
                $this->ended_at,

            'plannedDurationMinutes' =>
                $this->planned_duration_minutes,

            'plannedEndAt' =>
                $this->planned_end_at,

            'totalPausedMinutes' =>
                $this->total_paused_minutes,

            'durationMinutes' =>
                $this->duration_minutes,

            'hourlyPrice' =>
                $this->hourly_price,

            'expectedAmount' =>
                $this->expected_amount,

            'finalAmount' =>
                $this->final_amount,

            'status' =>
                $this->status->value,

            'pauses' =>
                ActivityUsagePauseResource::collection(
                    $this->whenLoaded('pauses')
                ),

            'startedBy' => $this->startedBy
                ? [
                    'id' => $this->startedBy->id,
                    'name' => $this->startedBy->name,
                ]
                : null,

            'closedBy' => $this->closedBy
                ? [
                    'id' => $this->closedBy->id,
                    'name' => $this->closedBy->name,
                ]
                : null,

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at
        ];
    }
}
