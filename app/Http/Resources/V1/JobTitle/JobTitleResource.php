<?php

namespace App\Http\Resources\V1\JobTitle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobTitleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['name'],
            'description' => $this->resource['description'],
        ];
    }
}
