<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\ActivityAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyStudentAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $session = $this['session'];
        $child = $this['child'];
        $attendance = $this['attendance'];
        $membership = $this['membership'] ?? null;

        return [
            'child' => [
                'id' => $child->id,
                'name' => $child->name,
            ],

            'activity' => [
                'id' => $session->activity_id,
                'name' => $session->activity?->name,
            ],

            'session' => [
                'id' => $session->id,
                'title' => $session->title,

                'sessionDate' =>
                    $session->session_date?->format('Y-m-d'),

                'startTime' => $session->start_time,

                'endTime' => $session->end_time,
            ],

            'activityMembershipId' =>
                $membership?->id,

            'attendance' => $attendance
                ? [
                    'id' => $attendance->id,

                    'status' =>
                        $attendance->status->value,

                    'checkInAt' =>
                        $attendance->check_in_at,

                    'checkOutAt' =>
                        $attendance->check_out_at,

                    'notes' =>
                        $attendance->notes,
                ]
                : null,

            'attendanceState' => $attendance
                ? strtolower(
                    $attendance->status->name
                )
                : 'pending',
        ];
    }
}
