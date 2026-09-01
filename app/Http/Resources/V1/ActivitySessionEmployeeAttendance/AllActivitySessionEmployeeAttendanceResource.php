<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\ActivitySessionEmployeeAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllActivitySessionEmployeeAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activitySessionId' => $this->activity_session_id,
            'employeeId' => $this->employee_id,
            'employeeName' => $this->employee?->name,
            'checkInAt' => $this->check_in_at,
            'checkOutAt' => $this->check_out_at,
            'lateMinutes' => $this->late_minutes,
            'status' => $this->status->value,
        ];
    }
}
