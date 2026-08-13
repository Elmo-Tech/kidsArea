<?php

namespace App\Http\Resources\V1\ActivitySession;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivitySessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'activity' => [
                'id' => $this->activity->id,
                'name' => $this->activity->name,
            ],

            'schedule' => $this->schedule
                ? [
                    'id' => $this->schedule->id,
                    'name' => $this->schedule->name,
                ]
                : null,

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

            'employees' =>
                $this->whenLoaded(
                    'employees',
                    fn () => $this->employees->map(
                        fn ($employee) => [
                            'id' => $employee->id,
                            'name' => $employee->name,

                            'jobTitle' => $employee->jobTitle
                                ? [
                                    'id' => $employee->jobTitle->id,
                                    'name' => $employee->jobTitle->name,
                                ]
                                : null,
                        ]
                    )
                ),

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at,

            'children' => $this->whenLoaded(
                'children',
                fn () => $this->children->map(
                    fn ($child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                    ]
                )
            ),
        ];
    }
}
