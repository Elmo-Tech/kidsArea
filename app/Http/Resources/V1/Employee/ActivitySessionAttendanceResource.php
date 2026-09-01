<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivitySessionAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'session' => [
                'id' => $this->id,

                'activity' => [
                    'id' => $this->activity_id,
                    'name' => $this->activity?->name,
                ],

                'title' => $this->title,

                'sessionDate' =>
                    $this->session_date?->format('Y-m-d'),

                'startTime' =>
                    $this->start_time,

                'endTime' =>
                    $this->end_time,

                'status' =>
                    $this->status->value,
            ],

            'children' => $this->children
                ->map(function ($child): array {

                    $attendance = $this->attendances
                        ->firstWhere(
                            'child_id',
                            $child->id
                        );

                    return [
                        'child' => [
                            'id' => $child->id,
                            'name' => $child->name,
                        ],

                        'attendance' => $attendance
                            ? [
                                'id' =>
                                    $attendance->id,

                                'status' =>
                                    $attendance->status->value,

                                'checkInAt' =>
                                    $attendance->check_in_at
                                        ,

                                'checkOutAt' =>
                                    $attendance->check_out_at
                                        ,

                                'notes' =>
                                    $attendance->notes,
                            ]
                            : null,
                    ];
                })
                ->values(),
        ];
    }
}
