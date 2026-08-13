<?php

namespace App\Http\Resources\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $role = $this->resource['user']->roles->first();
        $permissions = $role ? $role->permissions->pluck('name')->toArray() : [];
        return [
            'username' => $this->resource['user']->username,
            'avatar' => $this->resource['user']->avatar,
            'role' => $role ? $role->name : null,
            'permissions' => $permissions
        ];
    }
}
