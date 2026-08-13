<?php

namespace App\Http\Resources\V1\Employee;

use App\Http\Resources\V1\Department\DepartmentResource;
use App\Http\Resources\V1\EmployeeContract\EmployeeContractResource;
use App\Http\Resources\V1\JobTitle\JobTitleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
                return new DepartmentResource($this->department);
            }),
            'jobTitle' => $this->whenLoaded('jobTitle', function () {
                return new JobTitleResource($this->jobTitle);
            }),
            'nationalId' => $this->national_id,
            'createdAt' => $this->created_at,
            'currentContract' => $this->whenLoaded(
                'currentContract',
                fn () => new EmployeeContractResource($this->currentContract)
            ),
        ];
    }
}
