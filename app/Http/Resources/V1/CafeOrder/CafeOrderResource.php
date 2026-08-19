<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CafeOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CafeOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' =>
                $this->id,

            'orderNumber' =>
                $this->order_number,

            'visit' => $this->visit
                ? [
                    'id' => $this->visit->id,
                    'status' => $this->visit->status->value,
                ]
                : null,

            'items' =>
                CafeOrderItemResource::collection(
                    $this->whenLoaded('items')
                ),

            'subtotal' =>
                $this->subtotal,

            'discount' =>
                $this->discount,

            'total' =>
                $this->total,

            'status' =>
                $this->status->value,

            'confirmedAt' =>
                $this->confirmed_at,
            'completedAt' =>
                $this->completed_at,

            'cancelledAt' =>
                $this->cancelled_at,

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at
        ];
    }
}
