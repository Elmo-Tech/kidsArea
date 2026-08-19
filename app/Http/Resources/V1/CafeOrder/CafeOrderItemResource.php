<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\CafeOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CafeOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'product' => [
                'id' => $this->cafe_product_id,
                'name' => $this->product_name,
            ],

            'unitPrice' =>
                $this->unit_price,

            'quantity' =>
                $this->quantity,

            'total' =>
                $this->total,

            'notes' =>
                $this->notes,
        ];
    }
}
