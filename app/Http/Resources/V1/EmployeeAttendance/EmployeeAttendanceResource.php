<?php

namespace App\Http\Resources\V1\EmployeeAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'attendanceDate' => $this->attendance_date,

            'checkInAt' => $this->formatTime($this->check_in_at),
            'checkOutAt' => $this->formatTime($this->check_out_at),

            'workedMinutes' => $this->worked_minutes,

            'lateMinutes' => $this->late_minutes,
            'excusedLateMinutes' =>
                $this->excused_late_minutes,

            'earlyLeaveMinutes' =>
                $this->early_leave_minutes,

            'excusedEarlyLeaveMinutes' =>
                $this->excused_early_leave_minutes,

            'status' => $this->status->value,

            'notes' => $this->notes,
        ];
    }

    private function formatTime(?string $time): ?string
    {
        return $time
            ? substr($time, 0, 5)
            : null;
    }
}
