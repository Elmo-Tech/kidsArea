<?php

namespace App\Http\Resources\V1\ActivitySession;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllActivitySessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'activity' => [
                'id' => $this->activity->id,
                'name' => $this->activity->name,
            ],

            'sessionDate' =>
                $this->session_date?->format('Y-m-d'),

            'startTime' =>
                $this->start_time,

            'endTime' =>
                $this->end_time,

            'title' =>
                $this->title,

            'status' =>
                $this->status->value,

            'employeesCount' =>
                $this->employees_count ?? 0,
            'childrenCount' => $this->children_count ?? 0,
        ];
    }
}
