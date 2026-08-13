<?php

namespace App\Http\Resources\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefreshTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'accessToken' => $this->resource['accessToken'],
            'tokenType' => $this->resource['tokenType'],
            'expiresIn' => $this->resource['expiresIn'],
            'refreshToken' => $this->resource['refreshToken'],
            'refreshTokenExpiresIn' => $this->resource['refreshExpiresIn'],
        ];
    }
}
