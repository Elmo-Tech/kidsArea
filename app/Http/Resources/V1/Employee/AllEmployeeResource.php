<?php

namespace App\Http\Resources\V1\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllEmployeeResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'department' => $this->whenLoaded('department', function () {
                return $this->department ? [
                    'name' => $this->department->name,
                ] : null;
            }),
            'jobTitle' => $this->whenLoaded('jobTitle', function () {
                return $this->jobTitle ? [
                    'name' => $this->jobTitle->name,
                ] : null;
            }),
            'nationalId' => $this->national_id,
            'createdAt' => $this->created_at,
        ];
    }
}
