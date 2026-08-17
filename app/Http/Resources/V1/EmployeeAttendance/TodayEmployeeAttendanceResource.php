<?php

namespace App\Http\Resources\V1\EmployeeAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodayEmployeeAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attendance = $this['attendance'];

        return [
            'attendance' => $attendance
                ? [
                    'id' => $attendance->id,


                    'checkInAt' => $this->check_in_at,
                    'checkOutAt' => $this->check_out_at,

                    'workedMinutes' =>
                        $attendance->worked_minutes,

                    'lateMinutes' =>
                        $attendance->late_minutes,

                    'excusedLateMinutes' =>
                        $attendance->excused_late_minutes,

                    'earlyLeaveMinutes' =>
                        $attendance->early_leave_minutes,

                    'excusedEarlyLeaveMinutes' =>
                        $attendance
                            ->excused_early_leave_minutes,

                    'status' =>
                        $attendance->status->value,
                ]
                : null,

            'canCheckIn' =>
                $this['canCheckIn'],

            'canCheckOut' =>
                $this['canCheckOut'],
        ];
    }

    private function formatTime(
        ?string $time
    ): ?string {
        return $time
            ? substr($time, 0, 5)
            : null;
    }
}
