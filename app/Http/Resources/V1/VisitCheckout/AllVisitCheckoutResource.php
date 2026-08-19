<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\VisitCheckout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllVisitCheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visitId' => $this->visit_id,
            'total' => $this->total,
            'status' => $this->status->value,
            'createdAt' => $this->created_at
        ];
    }
}
