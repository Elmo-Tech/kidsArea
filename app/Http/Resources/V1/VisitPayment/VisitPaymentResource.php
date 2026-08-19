<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\VisitPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'visitCheckoutId' =>
                $this->visit_checkout_id,

            'amount' =>
                $this->amount,

            'paidAt' =>
                $this->paid_at,

            'reference' =>
                $this->reference,

            'notes' =>
                $this->notes,

            'createdAt' =>
                $this->created_at,
        ];
    }
}
