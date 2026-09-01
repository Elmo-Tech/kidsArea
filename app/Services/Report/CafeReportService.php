<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enums\CafeOrderStatusEnum;
use App\Models\CafeOrder;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CafeReportService
{
    public function report(array $data): array
    {
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $productId = isset($data['productId']) ? (int) $data['productId'] : null;

        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],

            'filters' => [
                'productId' => $productId,
            ],

            'summary' => $this->summary($from, $to),
            'products' => $this->products($from, $to, $productId),
            'directPayments' => $this->directPayments($from, $to),
        ];
    }

    private function summary(Carbon $from, Carbon $to): array
    {
        $row = CafeOrder::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw(
                'COUNT(*) as total_orders,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_orders,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed_orders,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_orders,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_orders,
                 COALESCE(SUM(CASE WHEN status = ? THEN total ELSE 0 END), 0) as completed_sales',
                [
                    CafeOrderStatusEnum::DRAFT->value,
                    CafeOrderStatusEnum::CONFIRMED->value,
                    CafeOrderStatusEnum::COMPLETED->value,
                    CafeOrderStatusEnum::CANCELLED->value,
                    CafeOrderStatusEnum::COMPLETED->value,
                ]
            )
            ->first();

        $completedOrders = (int) ($row->completed_orders ?? 0);
        $completedSales = round((float) ($row->completed_sales ?? 0), 2);

        return [
            'totalOrders' => (int) ($row->total_orders ?? 0),
            'draftOrders' => (int) ($row->draft_orders ?? 0),
            'confirmedOrders' => (int) ($row->confirmed_orders ?? 0),
            'completedOrders' => $completedOrders,
            'cancelledOrders' => (int) ($row->cancelled_orders ?? 0),
            'completedSales' => $completedSales,
            'averageOrderValue' => $completedOrders > 0
                ? round($completedSales / $completedOrders, 2)
                : 0,
        ];
    }

    private function products(
        Carbon $from,
        Carbon $to,
        ?int $productId
    ): array {
        return DB::table('cafe_order_items')
            ->join('cafe_orders', 'cafe_orders.id', '=', 'cafe_order_items.cafe_order_id')
            ->whereBetween('cafe_orders.created_at', [$from, $to])
            ->where('cafe_orders.status', CafeOrderStatusEnum::COMPLETED->value)
            ->when(
                $productId !== null,
                fn ($query) => $query->where('cafe_order_items.cafe_product_id', $productId)
            )
            ->selectRaw(
                'cafe_order_items.cafe_product_id,
                 cafe_order_items.product_name,
                 COUNT(DISTINCT cafe_orders.id) as orders_count,
                 COALESCE(SUM(cafe_order_items.quantity), 0) as quantity_sold,
                 COALESCE(SUM(cafe_order_items.quantity * cafe_order_items.unit_price), 0) as gross_sales'
            )
            ->groupBy(
                'cafe_order_items.cafe_product_id',
                'cafe_order_items.product_name'
            )
            ->orderByDesc('quantity_sold')
            ->get()
            ->map(fn ($row): array => [
                'productId' => $row->cafe_product_id ? (int) $row->cafe_product_id : null,
                'productName' => $row->product_name,
                'ordersCount' => (int) $row->orders_count,
                'quantitySold' => (float) $row->quantity_sold,
                'grossSales' => round((float) $row->gross_sales, 2),
            ])
            ->values()
            ->all();
    }

    private function directPayments(Carbon $from, Carbon $to): array
    {
        $payments = Payment::query()
            ->where('payable_type', CafeOrder::class)
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('COUNT(*) as payments_count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        $refunds = Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->where('payments.payable_type', CafeOrder::class)
            ->whereBetween('refunds.refunded_at', [$from, $to])
            ->selectRaw('COUNT(refunds.id) as refunds_count, COALESCE(SUM(refunds.amount), 0) as total_amount')
            ->first();

        $paid = round((float) ($payments->total_amount ?? 0), 2);
        $refunded = round((float) ($refunds->total_amount ?? 0), 2);

        return [
            'paymentsCount' => (int) ($payments->payments_count ?? 0),
            'refundsCount' => (int) ($refunds->refunds_count ?? 0),
            'grossCollected' => $paid,
            'refunds' => $refunded,
            'netCollected' => round($paid - $refunded, 2),
        ];
    }
}
