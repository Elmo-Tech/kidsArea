<?php

namespace App\Http\Resources\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
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
            'accessToken' => $this->resource['accessToken'],
            'tokenType' => $this->resource['tokenType'],
            'expiresIn' => $this->resource['expiresIn'],
            'refreshToken' => $this->resource['refreshToken'],
            'refreshTokenExpiresIn' => $this->resource['refreshExpiresIn'],
            'profile' => new UserProfileResource($this->resource),
        ];
    }
}
