<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Visit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllVisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'child' => $this->child
                ? [
                    'id' => $this->child->id,
                    'name' => $this->child->name,
                ]
                : null,

            'startedAt' =>
                $this->started_at,

            'closedAt' =>
                $this->closed_at,

            'status' =>
                $this->status->value,

            'activityUsagesCount' =>
                $this->activity_usages_count ?? 0,
        ];
    }
}
