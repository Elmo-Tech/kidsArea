<?php

namespace App\Http\Resources\V1\ActivityMembership;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityMembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

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

            'startDate' =>
                $this->start_date?->format('Y-m-d'),

            'endDate' =>
                $this->end_date?->format('Y-m-d'),

            'sessionsTotal' =>
                $this->sessions_total,

            'price' =>
                $this->price,

            'status' =>
                $this->status->value,

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at

        ];
    }
}
