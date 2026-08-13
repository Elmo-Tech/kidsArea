<?php

namespace App\Http\Resources\V1\EmployeePermission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllEmployeePermissionResource extends JsonResource
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
            'permissionDate' =>
                $this->permission_date?->format('Y-m-d'),
            'type' => $this->type->value,
            'fromTime' => $this->formatTime($this->from_time),
            'toTime' => $this->formatTime($this->to_time),
            'status' => $this->status->value,
            'approvedAt' =>
                $this->approved_at,
            'createdAt' =>
                $this->created_at,
        ];
    }
}
