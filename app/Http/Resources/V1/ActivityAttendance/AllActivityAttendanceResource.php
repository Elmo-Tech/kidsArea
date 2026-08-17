<?php

namespace App\Http\Resources\V1\ActivityAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllActivityAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'child' => [
                'id' => $this->child->id,
                'name' => $this->child->name,
            ],

            'activity' => [
                'id' => $this->session->activity->id,
                'name' => $this->session->activity->name,
            ],

            'sessionDate' =>
                $this->session->session_date?->format('Y-m-d'),

            'checkInAt' =>
                $this->check_in_at,

            'checkOutAt' =>
                $this->check_out_at,

            'status' =>
                $this->status->value,
        ];
    }
}
