<?php

namespace App\Http\Resources\V1\EmployeeSession;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'sessionDate' => $this->session_date?->format('Y-m-d'),

            'startTime' => $this->formatTime($this->start_time),
            'endTime' => $this->formatTime($this->end_time),

            'title' => $this->title,

            'status' => $this->status->value,

            'notes' => $this->notes,

            'createdAt' => $this->created_at?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function formatTime(?string $time): ?string
    {
        return $time
            ? substr($time, 0, 5)
            : null;
    }
}
