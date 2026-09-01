<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Invoice;

use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\VisitCheckout;
use App\Models\ActivityMembership;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoiceNumber' => $this->invoice_number,

            'type' => match (true) {
                $this->invoiceable instanceof VisitCheckout => 'visit',
                $this->invoiceable instanceof ActivityUsage => 'activity_usage',
                $this->invoiceable instanceof CafeOrder => 'cafe_order',
                $this->invoiceable instanceof ActivityMembership => 'activity_membership',
                default => null,
            },

            'invoiceableId' => $this->invoiceable_id,

            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,

            'issuedAt' => $this->issued_at,

            'details' => $this->resolveDetails(),

            'notes' => $this->notes,

            'createdAt' => $this->created_at,
        ];
    }

    private function resolveDetails(): array
    {
        return match (true) {
            $this->invoiceable instanceof VisitCheckout =>
                $this->visitCheckoutDetails(),

            $this->invoiceable instanceof ActivityUsage =>
                $this->activityUsageDetails(),

            $this->invoiceable instanceof CafeOrder =>
                $this->cafeOrderDetails(),

            $this->invoiceable instanceof ActivityMembership =>
                $this->activityMembershipDetails(),

            default => [],
        };
    }

    private function visitCheckoutDetails(): array
    {
        $checkout = $this->invoiceable;
        $visit = $checkout->visit;

        return [
            'visitId' => $visit->id,

            'child' => $visit->child
                ? [
                    'id' => $visit->child->id,
                    'name' => $visit->child->name,
                    'phone' => $visit->child->guardian_phone,
                ]
                : null,

            'activities' => $visit->activityUsages->map(function ($usage): array {
                return [
                    'id' => $usage->id,
                    'activityId' => $usage->activity_id,
                    'activityName' => $usage->activity?->name,
                    'durationMinutes' => $usage->duration_minutes,
                    'amount' => $usage->final_amount,
                ];
            }),

            'orders' => $visit->cafeOrders->map(function ($order): array {
                return [
                    'id' => $order->id,
                    'orderNumber' => $order->order_number,
                    'total' => $order->total,

                    'items' => $order->items->map(function ($item): array {
                        return [
                            'productName' => $item->product_name,
                            'quantity' => $item->quantity,
                            'unitPrice' => $item->unit_price,
                            'total' => $item->total,
                        ];
                    }),
                ];
            }),
        ];
    }

    private function activityUsageDetails(): array
    {
        $usage = $this->invoiceable;

        return [
            'activityUsageId' => $usage->id,

            'child' => $usage->child
                ? [
                    'id' => $usage->child->id,
                    'name' => $usage->child->name,
                    'phone' => $usage->child->phone,
                ]
                : null,

            'activity' => [
                'id' => $usage->activity_id,
                'name' => $usage->activity?->name,
            ],

            'startedAt' => $usage->started_at,
            'endedAt' => $usage->ended_at,
            'durationMinutes' => $usage->duration_minutes,
            'amount' => $usage->final_amount,
        ];
    }

    private function cafeOrderDetails(): array
    {
        $order = $this->invoiceable;

        return [
            'cafeOrderId' => $order->id,
            'orderNumber' => $order->order_number,

            'items' => $order->items->map(function ($item): array {
                return [
                    'productName' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->unit_price,
                    'total' => $item->total,
                ];
            }),
        ];
    }

    private function activityMembershipDetails(): array
    {
        $membership = $this->invoiceable;

        return [
            'membershipId' => $membership->id,

            'child' => $membership->child
                ? [
                    'id' => $membership->child->id,
                    'name' => $membership->child->name,
                ]
                : null,

            'activity' => [
                'id' => $membership->activity_id,
                'name' => $membership->activity?->name,
            ],

            'pricingPlan' => [
                'id' => $membership->pricing_plan_id,
                'name' => $membership->pricingPlan?->name,
            ],

            'price' => $membership->price,
            'startDate' => $membership->start_date,
            'endDate' => $membership->end_date,
        ];
    }
}
