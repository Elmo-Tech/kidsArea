<?php

namespace App\Http\Resources\V1\ActivitySchedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllActivityScheduleResource extends JsonResource
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

            'startDate' =>
                $this->start_date?->format('Y-m-d'),

            'endDate' =>
                $this->end_date?->format('Y-m-d'),

            'startTime' =>
                $this->start_time,

            'endTime' =>
                $this->end_time,

            //'weekDays' =>$this->week_days,

            'status' =>
                $this->status->value,

            'sessionsCount' =>
                $this->sessions_count ?? 0,
        ];
    }
}
