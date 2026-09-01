<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enums\PaymentMethodEnum;
use App\Models\ActivityMembership;
use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\VisitCheckout;
use Carbon\Carbon;

class FinancialReportService
{
    public function report(array $data): array
    {
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();

        $revenue = $this->revenue($from, $to);
        $refunds = $this->refunds($from, $to);
        $expenses = $this->expenses($from, $to);
        $paymentMethods = $this->paymentMethods($from, $to);

        $grossRevenue = $revenue['total'];
        $totalRefunds = $refunds['total'];
        $netRevenue = round($grossRevenue - $totalRefunds, 2);
        $netAfterExpenses = round($netRevenue - $expenses['total'], 2);

        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],

            'summary' => [
                'grossRevenue' => $grossRevenue,
                'refunds' => $totalRefunds,
                'netRevenue' => $netRevenue,
                'expenses' => $expenses['total'],
                'netAfterExpenses' => $netAfterExpenses,
            ],

            'revenue' => $revenue,
            'refunds' => $refunds,
            'expenses' => $expenses,
            'paymentMethods' => $paymentMethods,
        ];
    }

    private function revenue(Carbon $from, Carbon $to): array
    {
        $rows = Payment::query()
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('payable_type, COUNT(*) as payments_count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('payable_type')
            ->get()
            ->keyBy('payable_type');

        $memberships = $this->paymentTypeData($rows->get(ActivityMembership::class));
        $activityUsages = $this->paymentTypeData($rows->get(ActivityUsage::class));
        $cafeOrders = $this->paymentTypeData($rows->get(CafeOrder::class));
        $visits = $this->paymentTypeData($rows->get(VisitCheckout::class));

        return [
            'total' => round(
                $memberships['amount']
                + $activityUsages['amount']
                + $cafeOrders['amount']
                + $visits['amount'],
                2
            ),
            'paymentsCount' => $memberships['count']
                + $activityUsages['count']
                + $cafeOrders['count']
                + $visits['count'],
            'memberships' => $memberships,
            'activityUsages' => $activityUsages,
            'cafeOrders' => $cafeOrders,
            'visits' => $visits,
        ];
    }

    private function refunds(Carbon $from, Carbon $to): array
    {
        $rows = Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->whereBetween('refunds.refunded_at', [$from, $to])
            ->selectRaw('payments.payable_type, COUNT(*) as refunds_count, COALESCE(SUM(refunds.amount), 0) as total_amount')
            ->groupBy('payments.payable_type')
            ->get()
            ->keyBy('payable_type');

        $memberships = $this->refundTypeData($rows->get(ActivityMembership::class));
        $activityUsages = $this->refundTypeData($rows->get(ActivityUsage::class));
        $cafeOrders = $this->refundTypeData($rows->get(CafeOrder::class));
        $visits = $this->refundTypeData($rows->get(VisitCheckout::class));

        return [
            'total' => round(
                $memberships['amount']
                + $activityUsages['amount']
                + $cafeOrders['amount']
                + $visits['amount'],
                2
            ),
            'refundsCount' => $memberships['count']
                + $activityUsages['count']
                + $cafeOrders['count']
                + $visits['count'],
            'memberships' => $memberships,
            'activityUsages' => $activityUsages,
            'cafeOrders' => $cafeOrders,
            'visits' => $visits,
        ];
    }

    private function expenses(Carbon $from, Carbon $to): array
    {
        $summary = Expense::query()
            ->whereBetween('expense_at', [$from, $to])
            ->selectRaw('COUNT(*) as expenses_count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        $categories = Expense::query()
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->whereBetween('expenses.expense_at', [$from, $to])
            ->selectRaw('expense_categories.id, expense_categories.name, COUNT(expenses.id) as expenses_count, COALESCE(SUM(expenses.amount), 0) as total_amount')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'count' => (int) $row->expenses_count,
                'amount' => round((float) $row->total_amount, 2),
            ])
            ->values()
            ->all();

        return [
            'total' => round((float) ($summary->total_amount ?? 0), 2),
            'expensesCount' => (int) ($summary->expenses_count ?? 0),
            'byCategory' => $categories,
        ];
    }

    private function paymentMethods(Carbon $from, Carbon $to): array
    {
        $payments = Payment::query()
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('payment_method, COUNT(*) as payments_count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->keyBy(
                fn ($row) => $row->payment_method instanceof PaymentMethodEnum
                    ? $row->payment_method->value
                    : (int) $row->payment_method
            );

        $refunds = Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->whereBetween('refunds.refunded_at', [$from, $to])
            ->selectRaw('payments.payment_method, COUNT(refunds.id) as refunds_count, COALESCE(SUM(refunds.amount), 0) as total_amount')
            ->groupBy('payments.payment_method')
            ->get()
            ->keyBy(fn ($row) => (int) $row->payment_method);

        $methods = $payments->keys()
            ->merge($refunds->keys())
            ->unique()
            ->sort()
            ->values();

        return $methods->map(function ($method) use ($payments, $refunds): array {
            $payment = $payments->get($method);
            $refund = $refunds->get($method);

            $gross = round((float) ($payment->total_amount ?? 0), 2);
            $refunded = round((float) ($refund->total_amount ?? 0), 2);

            return [
                'paymentMethod' => (int) $method,
                'name' => PaymentMethodEnum::tryFrom((int) $method)?->name,
                'paymentsCount' => (int) ($payment->payments_count ?? 0),
                'refundsCount' => (int) ($refund->refunds_count ?? 0),
                'grossAmount' => $gross,
                'refundAmount' => $refunded,
                'netAmount' => round($gross - $refunded, 2),
            ];
        })->all();
    }

    private function paymentTypeData($row): array
    {
        return [
            'count' => (int) ($row->payments_count ?? 0),
            'amount' => round((float) ($row->total_amount ?? 0), 2),
        ];
    }

    private function refundTypeData($row): array
    {
        return [
            'count' => (int) ($row->refunds_count ?? 0),
            'amount' => round((float) ($row->total_amount ?? 0), 2),
        ];
    }
}
