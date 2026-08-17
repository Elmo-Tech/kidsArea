<?php

namespace App\Http\Resources\V1\ActivityUsage;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityUsagePauseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'pausedAt' =>
                $this->paused_at,

            'resumedAt' =>
                $this->resumed_at,

            'durationMinutes' =>
                $this->duration_minutes,
        ];
    }
}
