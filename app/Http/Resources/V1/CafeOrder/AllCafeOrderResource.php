<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CafeOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllCafeOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' =>
                $this->id,

            'orderNumber' =>
                $this->order_number,

            'visitId' =>
                $this->visit_id,

            'itemsCount' =>
                $this->items_count ?? 0,

            'subtotal' =>
                $this->subtotal,

            'discount' =>
                $this->discount,

            'total' =>
                $this->total,

            'status' =>
                $this->status->value,

            'createdAt' =>
                $this->created_at
        ];
    }
}
