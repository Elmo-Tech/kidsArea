<?php

namespace App\Http\Resources\V1\EmployeeSession;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllEmployeeSessionResource extends JsonResource
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

            'sessionDate' => $this->session_date?->format('Y-m-d'),

            'startTime' => $this->formatTime($this->start_time),
            'endTime' => $this->formatTime($this->end_time),

            'title' => $this->title,

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
