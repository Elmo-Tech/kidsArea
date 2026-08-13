<?php

namespace App\Http\Resources\V1\EmployeeLeave;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'leaveType' => [
                'id' => $this->leaveType->id,
                'name' => $this->leaveType->name,
            ],

            'startDate' => $this->start_date,
            'endDate' => $this->end_date,

            'daysCount' => $this->days_count,

            'reason' => $this->reason,

            'status' => $this->status->value,

            'approvedBy' => $this->approvedBy
                ? [
                    'id' => $this->approvedBy->id,
                    'username' => $this->approvedBy->username,
                ]
                : null,

            'approvedAt' => $this->approved_at,

            'notes' => $this->notes,

            'createdAt' => $this->created_at,
        ];
    }
}
