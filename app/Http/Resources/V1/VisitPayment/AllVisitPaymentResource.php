<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\VisitPayment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllVisitPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'paidAt' => $this->paid_at,
            'reference' => $this->reference,
        ];
    }
}
