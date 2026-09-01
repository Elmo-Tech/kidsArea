<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Enums\ActivitySessionStatusEnum;
use App\Enums\ActivityUsageStatusEnum;
use App\Enums\CafeOrderStatusEnum;
use App\Enums\VisitStatusEnum;
use App\Models\ActivityAttendance;
use App\Models\ActivityMembership;
use App\Models\ActivitySession;
use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Visit;
use App\Models\VisitCheckout;
use App\Services\Cash\CashSummaryService;

class DashboardService
{
    public function __construct(
        private readonly CashSummaryService $cashSummaryService
    ) {}

    public function overview(): array
    {
        $cash = $this->cash();
        $today = $this->today();
        $attendance = $this->attendance();
        $revenue = $this->revenue();
        $refunds = $this->refunds();
        $expenses = $this->expenses();

        $netRevenue = round($revenue['today'] - $refunds['today'], 2);
        $netAfterExpenses = round($netRevenue - $expenses['today'], 2);

        return [
            'cash' => $cash,
            'today' => $today,
            'attendance' => $attendance,
            'revenue' => $revenue,
            'refunds' => $refunds,
            'expenses' => $expenses,
            'financial' => [
                'grossRevenue' => $revenue['today'],
                'refunds' => $refunds['today'],
                'netRevenue' => $netRevenue,
                'expenses' => $expenses['today'],
                'netAfterExpenses' => $netAfterExpenses,
            ],
            'needsAttention' => [
                'openVisits' => $today['openVisits'],
                'activeUsages' => $today['activeUsages'],
                'pausedUsages' => $today['pausedUsages'],
                'pendingCafeOrders' => $today['pendingCafeOrders'],
                'unmarkedAttendance' => $attendance['notMarked'],
                'openCashShifts' => $cash['openCashShifts'],
                'pendingCashTransfers' => $cash['pendingTransfersCount'],
            ],
        ];
    }

    private function cash(): array
    {
        $summary = $this->cashSummaryService->general();

        return [
            'totalCash' => round((float) ($summary['totalCash'] ?? 0), 2),
            'mainRegisterBalance' => round((float) ($summary['mainRegisterBalance'] ?? 0), 2),
            'cashierRegistersBalance' => round((float) ($summary['cashierRegistersBalance'] ?? 0), 2),
            'openCashShifts' => (int) ($summary['openRegistersCount'] ?? 0),
            'pendingTransfersCount' => (int) ($summary['pendingTransfersCount'] ?? 0),
            'pendingTransfersBalance' => round((float) ($summary['pendingTransfersBalance'] ?? 0), 2),
        ];
    }

    private function today(): array
    {
        return [
            'visits' => Visit::query()
                ->whereDate('started_at', today())
                ->count(),

            'openVisits' => Visit::query()
                ->where('status', VisitStatusEnum::OPEN->value)
                ->count(),

            'activitySessions' => ActivitySession::query()
                ->whereDate('session_date', today())
                ->where('status', '!=', ActivitySessionStatusEnum::CANCELLED->value)
                ->count(),

            'scheduledSessions' => ActivitySession::query()
                ->whereDate('session_date', today())
                ->where('status', ActivitySessionStatusEnum::SCHEDULED->value)
                ->count(),

            'activeUsages' => ActivityUsage::query()
                ->where('status', ActivityUsageStatusEnum::ACTIVE->value)
                ->count(),

            'pausedUsages' => ActivityUsage::query()
                ->where('status', ActivityUsageStatusEnum::PAUSED->value)
                ->count(),

            'cafeOrders' => CafeOrder::query()
                ->whereDate('created_at', today())
                ->where('status', '!=', CafeOrderStatusEnum::CANCELLED->value)
                ->count(),

            'pendingCafeOrders' => CafeOrder::query()
                ->whereIn('status', [
                    CafeOrderStatusEnum::DRAFT->value,
                    CafeOrderStatusEnum::CONFIRMED->value,
                ])
                ->count(),

            'newMemberships' => ActivityMembership::query()
                ->whereDate('created_at', today())
                ->whereNull('renewed_from_membership_id')
                ->count(),

            'renewedMemberships' => ActivityMembership::query()
                ->whereDate('created_at', today())
                ->whereNotNull('renewed_from_membership_id')
                ->count(),
        ];
    }

    private function attendance(): array
    {
        $sessionIds = ActivitySession::query()
            ->whereDate('session_date', today())
            ->where('status', '!=', ActivitySessionStatusEnum::CANCELLED->value)
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return [
                'expectedChildren' => 0,
                'present' => 0,
                'absent' => 0,
                'excused' => 0,
                'notMarked' => 0,
            ];
        }

        $expectedChildren = ActivitySession::query()
            ->whereIn('id', $sessionIds)
            ->withCount('children')
            ->get()
            ->sum('children_count');

        $attendance = ActivityAttendance::query()
            ->whereIn('activity_session_id', $sessionIds)
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as absent,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as excused',
                [
                    ActivityAttendanceStatusEnum::PRESENT->value,
                    ActivityAttendanceStatusEnum::ABSENT->value,
                    ActivityAttendanceStatusEnum::EXCUSED->value,
                ]
            )
            ->first();

        $present = (int) ($attendance->present ?? 0);
        $absent = (int) ($attendance->absent ?? 0);
        $excused = (int) ($attendance->excused ?? 0);
        $marked = $present + $absent + $excused;

        return [
            'expectedChildren' => (int) $expectedChildren,
            'present' => $present,
            'absent' => $absent,
            'excused' => $excused,
            'notMarked' => max(0, (int) $expectedChildren - $marked),
        ];
    }

    private function revenue(): array
    {
        $payments = Payment::query()
            ->whereDate('paid_at', today())
            ->selectRaw('payable_type, COUNT(*) as payments_count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('payable_type')
            ->get()
            ->keyBy('payable_type');

        $memberships = round((float) ($payments->get(ActivityMembership::class)?->total_amount ?? 0), 2);
        $activityUsages = round((float) ($payments->get(ActivityUsage::class)?->total_amount ?? 0), 2);
        $cafeOrders = round((float) ($payments->get(CafeOrder::class)?->total_amount ?? 0), 2);
        $visits = round((float) ($payments->get(VisitCheckout::class)?->total_amount ?? 0), 2);

        return [
            'today' => round($memberships + $activityUsages + $cafeOrders + $visits, 2),
            'paymentsCount' => (int) $payments->sum('payments_count'),
            'memberships' => $memberships,
            'activityUsages' => $activityUsages,
            'cafeOrders' => $cafeOrders,
            'visits' => $visits,
        ];
    }

    private function refunds(): array
    {
        $refunds = Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->whereDate('refunds.refunded_at', today())
            ->selectRaw('payments.payable_type, COUNT(*) as refunds_count, COALESCE(SUM(refunds.amount), 0) as total_amount')
            ->groupBy('payments.payable_type')
            ->get()
            ->keyBy('payable_type');

        $memberships = round((float) ($refunds->get(ActivityMembership::class)?->total_amount ?? 0), 2);
        $activityUsages = round((float) ($refunds->get(ActivityUsage::class)?->total_amount ?? 0), 2);
        $cafeOrders = round((float) ($refunds->get(CafeOrder::class)?->total_amount ?? 0), 2);
        $visits = round((float) ($refunds->get(VisitCheckout::class)?->total_amount ?? 0), 2);

        return [
            'today' => round($memberships + $activityUsages + $cafeOrders + $visits, 2),
            'refundsCount' => (int) $refunds->sum('refunds_count'),
            'memberships' => $memberships,
            'activityUsages' => $activityUsages,
            'cafeOrders' => $cafeOrders,
            'visits' => $visits,
        ];
    }

    private function expenses(): array
    {
        $expenses = Expense::query()
            ->whereDate('expense_at', today())
            ->selectRaw('COUNT(*) as expenses_count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        return [
            'today' => round((float) ($expenses->total_amount ?? 0), 2),
            'expensesCount' => (int) ($expenses->expenses_count ?? 0),
        ];
    }
}
