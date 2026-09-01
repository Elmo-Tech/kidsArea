<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyActivitySessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attendance = $this->employeeAttendances->first();

        return [
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

            'attendance' => $attendance
                ? [
                    'id' => $attendance->id,

                    'checkInAt' =>
                        $attendance->check_in_at?->format(
                            'Y-m-d H:i:s'
                        ),

                    'checkOutAt' =>
                        $attendance->check_out_at?->format(
                            'Y-m-d H:i:s'
                        ),

                    'lateMinutes' =>
                        $attendance->late_minutes,

                    'status' =>
                        $attendance->status->value,
                ]
                : null,

            'notes' => $this->notes,
        ];
    }
}
