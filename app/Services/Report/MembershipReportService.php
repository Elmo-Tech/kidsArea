<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enums\ActivityMembershipStatusEnum;
use App\Models\ActivityMembership;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MembershipReportService
{
    public function report(array $data): array
    {
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $activityId = isset($data['activityId']) ? (int) $data['activityId'] : null;

        $this->expireEndedSubscriptions();

        $memberships = $this->membershipsQuery($from, $to, $activityId);

        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'filters' => [
                'activityId' => $activityId,
            ],
            'summary' => $this->summary(clone $memberships),
            'paymentStatus' => $this->paymentStatus(clone $memberships),
            'financial' => $this->financial($from, $to, $activityId),
            'byActivity' => $this->byActivity($from, $to, $activityId),
        ];
    }

    private function membershipsQuery(
        Carbon $from,
        Carbon $to,
        ?int $activityId
    ): Builder {
        return ActivityMembership::query()
            ->whereBetween('created_at', [$from, $to])
            ->when(
                $activityId !== null,
                fn (Builder $query): Builder => $query->where('activity_id', $activityId)
            );
    }

    private function summary(Builder $query): array
    {
        $row = $query
            ->selectRaw(
                'COUNT(*) as total,
                 SUM(CASE WHEN renewed_from_membership_id IS NULL THEN 1 ELSE 0 END) as new_memberships,
                 SUM(CASE WHEN renewed_from_membership_id IS NOT NULL THEN 1 ELSE 0 END) as renewed_memberships,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as expired,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                 COALESCE(SUM(price), 0) as total_value',
                [
                    ActivityMembershipStatusEnum::INACTIVE->value,
                    ActivityMembershipStatusEnum::ACTIVE->value,
                    ActivityMembershipStatusEnum::EXPIRED->value,
                    ActivityMembershipStatusEnum::CANCELLED->value,
                ]
            )
            ->first();

        return [
            'totalMemberships' => (int) ($row->total ?? 0),
            'newMemberships' => (int) ($row->new_memberships ?? 0),
            'renewedMemberships' => (int) ($row->renewed_memberships ?? 0),
            'inactive' => (int) ($row->inactive ?? 0),
            'active' => (int) ($row->active ?? 0),
            'expired' => (int) ($row->expired ?? 0),
            'cancelled' => (int) ($row->cancelled ?? 0),
            'totalValue' => round((float) ($row->total_value ?? 0), 2),
        ];
    }

    private function paymentStatus(Builder $memberships): array
    {
        $membershipIds = $memberships->pluck('id');

        if ($membershipIds->isEmpty()) {
            return [
                'fullyPaid' => 0,
                'partiallyPaid' => 0,
                'unpaid' => 0,
                'totalRemaining' => 0,
            ];
        }

        $paymentsSubQuery = Payment::query()
            ->select('payable_id')
            ->selectRaw('COALESCE(SUM(amount), 0) as paid')
            ->where('payable_type', ActivityMembership::class)
            ->whereIn('payable_id', $membershipIds)
            ->groupBy('payable_id');

        $refundsSubQuery = Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->select('payments.payable_id')
            ->selectRaw('COALESCE(SUM(refunds.amount), 0) as refunded')
            ->where('payments.payable_type', ActivityMembership::class)
            ->whereIn('payments.payable_id', $membershipIds)
            ->groupBy('payments.payable_id');

        $rows = ActivityMembership::query()
            ->whereIn('activity_memberships.id', $membershipIds)
            ->leftJoinSub(
                $paymentsSubQuery,
                'payment_totals',
                'payment_totals.payable_id',
                '=',
                'activity_memberships.id'
            )
            ->leftJoinSub(
                $refundsSubQuery,
                'refund_totals',
                'refund_totals.payable_id',
                '=',
                'activity_memberships.id'
            )
            ->get([
                'activity_memberships.id',
                'activity_memberships.price',
                DB::raw('COALESCE(payment_totals.paid, 0) as paid'),
                DB::raw('COALESCE(refund_totals.refunded, 0) as refunded'),
            ]);

        $fullyPaid = 0;
        $partiallyPaid = 0;
        $unpaid = 0;
        $totalRemaining = 0;

        foreach ($rows as $row) {
            $price = round((float) $row->price, 2);
            $netPaid = max(0, round((float) $row->paid - (float) $row->refunded, 2));
            $remaining = max(0, round($price - $netPaid, 2));

            $totalRemaining += $remaining;

            if ($price > 0 && $netPaid >= $price) {
                $fullyPaid++;
                continue;
            }

            if ($netPaid > 0) {
                $partiallyPaid++;
                continue;
            }

            $unpaid++;
        }

        return [
            'fullyPaid' => $fullyPaid,
            'partiallyPaid' => $partiallyPaid,
            'unpaid' => $unpaid,
            'totalRemaining' => round($totalRemaining, 2),
        ];
    }

    private function financial(
        Carbon $from,
        Carbon $to,
        ?int $activityId
    ): array {
        $membershipIds = ActivityMembership::query()
            ->select('id')
            ->when(
                $activityId !== null,
                fn (Builder $query): Builder => $query->where('activity_id', $activityId)
            );

        $membershipValue = ActivityMembership::query()
            ->whereBetween('created_at', [$from, $to])
            ->when(
                $activityId !== null,
                fn (Builder $query): Builder => $query->where('activity_id', $activityId)
            )
            ->sum('price');

        $payments = Payment::query()
            ->where('payable_type', ActivityMembership::class)
            ->whereIn('payable_id', clone $membershipIds)
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $refunds = Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->where('payments.payable_type', ActivityMembership::class)
            ->whereIn('payments.payable_id', clone $membershipIds)
            ->whereBetween('refunds.refunded_at', [$from, $to])
            ->sum('refunds.amount');

        return [
            'membershipValue' => round((float) $membershipValue, 2),
            'payments' => round((float) $payments, 2),
            'refunds' => round((float) $refunds, 2),
            'netPaid' => round((float) $payments - (float) $refunds, 2),
        ];
    }

    private function byActivity(
        Carbon $from,
        Carbon $to,
        ?int $activityId
    ): array {
        return ActivityMembership::query()
            ->join('activities', 'activities.id', '=', 'activity_memberships.activity_id')
            ->whereBetween('activity_memberships.created_at', [$from, $to])
            ->when(
                $activityId !== null,
                fn (Builder $query): Builder => $query->where('activity_memberships.activity_id', $activityId)
            )
            ->selectRaw(
                'activities.id,
                 activities.name,
                 COUNT(activity_memberships.id) as memberships_count,
                 SUM(CASE WHEN activity_memberships.renewed_from_membership_id IS NULL THEN 1 ELSE 0 END) as new_memberships,
                 SUM(CASE WHEN activity_memberships.renewed_from_membership_id IS NOT NULL THEN 1 ELSE 0 END) as renewed_memberships,
                 COALESCE(SUM(activity_memberships.price), 0) as membership_value'
            )
            ->groupBy('activities.id', 'activities.name')
            ->orderByDesc('memberships_count')
            ->get()
            ->map(fn ($row): array => [
                'activityId' => (int) $row->id,
                'activityName' => $row->name,
                'membershipsCount' => (int) $row->memberships_count,
                'newMemberships' => (int) $row->new_memberships,
                'renewedMemberships' => (int) $row->renewed_memberships,
                'membershipValue' => round((float) $row->membership_value, 2),
            ])
            ->values()
            ->all();
    }

    private function expireEndedSubscriptions(): void
    {
        ActivityMembership::query()
            ->where('status', ActivityMembershipStatusEnum::ACTIVE->value)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', today())
            ->update([
                'status' => ActivityMembershipStatusEnum::EXPIRED->value,
            ]);
    }
}
