<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Visit;

use App\Http\Resources\V1\ActivityUsage\AllActivityUsageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
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

            'activityUsages' =>
                AllActivityUsageResource::collection(
                    $this->whenLoaded('activityUsages')
                ),

            'startedBy' => $this->startedBy
                ? [
                    'id' => $this->startedBy->id,
                    'name' => $this->startedBy->name,
                ]
                : null,

            'closedBy' => $this->closedBy
                ? [
                    'id' => $this->closedBy->id,
                    'name' => $this->closedBy->name,
                ]
                : null,

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at,

            'updatedAt' =>
                $this->updated_at
        ];
    }
}
