<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'status' => $this->status,
            'roleId' => $this->roles?->first()?->id??null,
            'employeeId' => $this->employee_id,
        ];
    }
}
