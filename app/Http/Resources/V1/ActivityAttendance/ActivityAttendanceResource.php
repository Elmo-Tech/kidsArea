<?php

namespace App\Http\Resources\V1\ActivityAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'session' => [
                'id' => $this->session->id,
                'sessionDate' =>
                    $this->session->session_date?->format('Y-m-d'),
                'startTime' =>
                    $this->session->start_time,
                'endTime' =>
                    $this->session->end_time,
            ],

            'activity' => [
                'id' => $this->session->activity->id,
                'name' => $this->session->activity->name,
            ],

            'child' => [
                'id' => $this->child->id,
                'name' => $this->child->name,
            ],

            'membership' => $this->membership
                    ? [
                        'id' => $this->membership->id,
                        'pricingPlanId' => $this->membership->activity_pricing_plan_id,
                        'pricingPlanType' => $this->membership->pricingPlan?->type?->value,
                        'status' => $this->membership->status->value,
                        'sessionsTotal' => $this->membership->sessions_total,
                    ]
                    : null,

            'checkInAt' =>
                $this->check_in_at,

            'checkOutAt' =>
                $this->check_out_at,

            'status' =>
                $this->status->value,

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at


        ];
    }
}
