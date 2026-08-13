<?php

namespace App\Http\Resources\V1\LeaveType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllLeaveTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'createdAt' => $this->created_at
        ];
    }
}
