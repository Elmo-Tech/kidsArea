<?php

namespace App\Http\Resources\V1\ActivityMembership;

use App\Http\Resources\V1\Invoice\InvoiceResource;
use App\Http\Resources\V1\Payment\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityMembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'child' => [
                'id' => $this->child->id,
                'name' => $this->child->name,
            ],

            'activity' => [
                'id' => $this->activity->id,
                'name' => $this->activity->name,
            ],

            'pricingPlan' => [
                'id' => $this->pricingPlan->id,
                'name' => $this->pricingPlan->name,
                'type' => $this->pricingPlan->type->value,
            ],

            'startDate' => $this->start_date?->format('Y-m-d'),
            'endDate' => $this->end_date?->format('Y-m-d'),

            'sessionsTotal' => $this->sessions_total,

            'sessionsUsed' => $this->when(
                isset($this->sessions_used),
                fn () => (int) $this->sessions_used
            ),

            'sessionsRemaining' => $this->when(
                isset($this->sessions_remaining),
                fn () => (int) $this->sessions_remaining
            ),

            'price' => $this->price,
            'status' => $this->status->value,
            'notes' => $this->notes,

            'paymentSummary' => $this->when(
                isset($this->payment_summary),
                fn () => $this->payment_summary
            ),

            'payments' => PaymentResource::collection(
                $this->whenLoaded('payments')
            ),

            'invoice' => $this->whenLoaded(
                'invoice',
                fn () => $this->invoice
                    ? new InvoiceResource($this->invoice)
                    : null
            ),

            'renewedFromMembershipId' =>
                $this->renewed_from_membership_id
                    ? (int) $this->renewed_from_membership_id
                    : null,

            'renewalId' => $this->whenLoaded(
                'renewal',
                fn () => $this->renewal?->id
            ),

            'createdAt' => $this->created_at,
        ];
    }
}
