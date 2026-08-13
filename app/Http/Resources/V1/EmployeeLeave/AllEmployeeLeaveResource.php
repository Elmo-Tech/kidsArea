<?php

namespace App\Http\Resources\V1\EmployeeLeave;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllEmployeeLeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'employee' => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,

                'jobTitle' => $this->employee->jobTitle
                    ? [
                        'id' => $this->employee->jobTitle->id,
                        'name' => $this->employee->jobTitle->name,
                    ]
                    : null,
            ],

            'leaveType' => [
                'id' => $this->leaveType->id,
                'name' => $this->leaveType->name,
            ],

            'startDate' => $this->start_date,
            'endDate' => $this->end_date,

            'daysCount' => $this->days_count,

            'status' => $this->status,

            'approvedBy' => $this->approvedBy
                ? [
                    'id' => $this->approvedBy->id,
                    'username' => $this->approvedBy->username,
                ]
                : null,

            'approvedAt' => $this->approved_at,
        ];
    }
}
