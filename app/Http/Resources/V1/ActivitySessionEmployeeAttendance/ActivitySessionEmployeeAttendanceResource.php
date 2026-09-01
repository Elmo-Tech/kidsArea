<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\ActivitySessionEmployeeAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivitySessionEmployeeAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'session' => [
                'id' => $this->activity_session_id,
                'title' => $this->session?->title,
                'sessionDate' => $this->session?->session_date?->format('Y-m-d'),
                'startTime' => $this->session?->start_time,
                'endTime' => $this->session?->end_time,
            ],

            'employee' => [
                'id' => $this->employee_id,
                'name' => $this->employee?->name,
                'jobTitle' => $this->employee?->jobTitle?->name,
            ],

            'checkInAt' => $this->check_in_at,
            'checkOutAt' => $this->check_out_at,
            'lateMinutes' => $this->late_minutes,
            'status' => $this->status->value,
            'notes' => $this->notes,

            'createdAt' => $this->created_at
        ];
    }
}
