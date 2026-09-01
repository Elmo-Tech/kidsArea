<?php

namespace App\Http\Resources\V1\ActivityUsage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllActivityUsageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'child' => when($this->child, function () {
                return [
                    'id' => $this->child->id,
                    'name' => $this->child->name,
                ];
            }),
            'activity' => [
                'id' => $this->activity->id,
                'name' => $this->activity->name,
            ],

            'usageType' =>
                $this->usage_type->value,

            'startedAt' =>
                $this->started_at,

            'endedAt' =>
                $this->ended_at,

            'plannedDurationMinutes' =>
                $this->planned_duration_minutes,

            'totalPausedMinutes' =>
                $this->total_paused_minutes,

            'durationMinutes' =>
                $this->duration_minutes,

            'expectedAmount' =>
                $this->expected_amount,

            'finalAmount' =>
                $this->final_amount,

            'status' =>
                $this->status->value,
        ];
    }
}
