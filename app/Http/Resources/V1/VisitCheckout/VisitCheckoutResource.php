<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\VisitCheckout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitCheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'visit' => [
                'id' => $this->visit_id,
            ],

            'activityTotal' => $this->activity_total,
            'cafeTotal' => $this->cafe_total,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,

            'status' => $this->status->value,

            'finalizedAt' => $this->finalized_at,
            'cancelledAt' => $this->cancelled_at,

            'notes' => $this->notes,

            'createdAt' => $this->created_at,
        ];
    }
}
