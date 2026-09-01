<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Enums\ActivitySessionStatusEnum;
use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Models\ActivityMembership;
use App\Models\ActivitySession;
use App\Models\ActivityUsage;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ActivityReportService
{
    public function report(array $data): array
    {
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $activityId = isset($data['activityId']) ? (int) $data['activityId'] : null;

        $activities = Activity::query()
            ->when($activityId !== null, fn ($query) => $query->whereKey($activityId))
            ->get(['id', 'name']);

        $sessions = $this->sessions($from, $to, $activityId);
        $attendance = $this->attendance($from, $to, $activityId);
        $memberships = $this->memberships($from, $to, $activityId);
        $usages = $this->usages($from, $to, $activityId);

        $membershipPayments = $this->membershipPayments($from, $to, $activityId);
        $membershipRefunds = $this->membershipRefunds($from, $to, $activityId);
        $usagePayments = $this->usagePayments($from, $to, $activityId);
        $usageRefunds = $this->usageRefunds($from, $to, $activityId);

        $rows = $activities->map(function (Activity $activity) use (
            $sessions,
            $attendance,
            $memberships,
            $usages,
            $membershipPayments,
            $membershipRefunds,
            $usagePayments,
            $usageRefunds
        ): array {
            $session = $sessions->get($activity->id);
            $attendanceRow = $attendance->get($activity->id);
            $membership = $memberships->get($activity->id);
            $usage = $usages->get($activity->id);

            $membershipGross = round((float) ($membershipPayments->get($activity->id)?->total_amount ?? 0), 2);
            $membershipRefunded = round((float) ($membershipRefunds->get($activity->id)?->total_amount ?? 0), 2);
            $membershipNet = round($membershipGross - $membershipRefunded, 2);

            $usageGross = round((float) ($usagePayments->get($activity->id)?->total_amount ?? 0), 2);
            $usageRefunded = round((float) ($usageRefunds->get($activity->id)?->total_amount ?? 0), 2);
            $usageNet = round($usageGross - $usageRefunded, 2);

            return [
                'activityId' => $activity->id,
                'activityName' => $activity->name,

                'sessions' => [
                    'total' => (int) ($session->total ?? 0),
                    'scheduled' => (int) ($session->scheduled ?? 0),
                    'completed' => (int) ($session->completed ?? 0),
                    'cancelled' => (int) ($session->cancelled ?? 0),
                ],

                'attendance' => [
                    'expected' => (int) ($session->expected_children ?? 0),
                    'present' => (int) ($attendanceRow->present ?? 0),
                    'absent' => (int) ($attendanceRow->absent ?? 0),
                    'excused' => (int) ($attendanceRow->excused ?? 0),
                ],

                'memberships' => [
                    'count' => (int) ($membership->memberships_count ?? 0),
                    'new' => (int) ($membership->new_memberships ?? 0),
                    'renewed' => (int) ($membership->renewed_memberships ?? 0),
                    'value' => round((float) ($membership->membership_value ?? 0), 2),
                ],

                'usages' => [
                    'count' => (int) ($usage->usages_count ?? 0),
                ],

                'revenue' => [
                    'memberships' => [
                        'gross' => $membershipGross,
                        'refunds' => $membershipRefunded,
                        'net' => $membershipNet,
                    ],
                    'activityUsages' => [
                        'gross' => $usageGross,
                        'refunds' => $usageRefunded,
                        'net' => $usageNet,
                    ],
                    'gross' => round($membershipGross + $usageGross, 2),
                    'refunds' => round($membershipRefunded + $usageRefunded, 2),
                    'net' => round($membershipNet + $usageNet, 2),
                ],
            ];
        })->values();

        return [
            'period' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'filters' => [
                'activityId' => $activityId,
            ],
            'summary' => $this->summary($rows),
            'activities' => $rows->all(),
        ];
    }

    private function sessions(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return ActivitySession::query()
            ->leftJoin('activity_session_children', 'activity_session_children.activity_session_id', '=', 'activity_sessions.id')
            ->whereBetween('activity_sessions.session_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->when($activityId !== null, fn ($query) => $query->where('activity_sessions.activity_id', $activityId))
            ->selectRaw(
                'activity_sessions.activity_id,
                 COUNT(DISTINCT activity_sessions.id) as total,
                 COUNT(DISTINCT CASE WHEN activity_sessions.status = ? THEN activity_sessions.id END) as scheduled,
                 COUNT(DISTINCT CASE WHEN activity_sessions.status = ? THEN activity_sessions.id END) as completed,
                 COUNT(DISTINCT CASE WHEN activity_sessions.status = ? THEN activity_sessions.id END) as cancelled,
                 COUNT(CASE WHEN activity_sessions.status != ? THEN activity_session_children.child_id END) as expected_children',
                [
                    ActivitySessionStatusEnum::SCHEDULED->value,
                    ActivitySessionStatusEnum::COMPLETED->value,
                    ActivitySessionStatusEnum::CANCELLED->value,
                    ActivitySessionStatusEnum::CANCELLED->value,
                ]
            )
            ->groupBy('activity_sessions.activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function attendance(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return ActivityAttendance::query()
            ->join('activity_sessions', 'activity_sessions.id', '=', 'activity_attendances.activity_session_id')
            ->whereBetween('activity_sessions.session_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->where('activity_sessions.status', '!=', ActivitySessionStatusEnum::CANCELLED->value)
            ->when($activityId !== null, fn ($query) => $query->where('activity_sessions.activity_id', $activityId))
            ->selectRaw(
                'activity_sessions.activity_id,
                 SUM(CASE WHEN activity_attendances.status = ? THEN 1 ELSE 0 END) as present,
                 SUM(CASE WHEN activity_attendances.status = ? THEN 1 ELSE 0 END) as absent,
                 SUM(CASE WHEN activity_attendances.status = ? THEN 1 ELSE 0 END) as excused',
                [
                    ActivityAttendanceStatusEnum::PRESENT->value,
                    ActivityAttendanceStatusEnum::ABSENT->value,
                    ActivityAttendanceStatusEnum::EXCUSED->value,
                ]
            )
            ->groupBy('activity_sessions.activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function memberships(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return ActivityMembership::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($activityId !== null, fn ($query) => $query->where('activity_id', $activityId))
            ->selectRaw(
                'activity_id,
                 COUNT(*) as memberships_count,
                 SUM(CASE WHEN renewed_from_membership_id IS NULL THEN 1 ELSE 0 END) as new_memberships,
                 SUM(CASE WHEN renewed_from_membership_id IS NOT NULL THEN 1 ELSE 0 END) as renewed_memberships,
                 COALESCE(SUM(price), 0) as membership_value'
            )
            ->groupBy('activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function usages(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return ActivityUsage::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($activityId !== null, fn ($query) => $query->where('activity_id', $activityId))
            ->selectRaw('activity_id, COUNT(*) as usages_count')
            ->groupBy('activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function membershipPayments(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return Payment::query()
            ->join('activity_memberships', function ($join): void {
                $join->on('activity_memberships.id', '=', 'payments.payable_id')
                    ->where('payments.payable_type', ActivityMembership::class);
            })
            ->whereBetween('payments.paid_at', [$from, $to])
            ->when($activityId !== null, fn ($query) => $query->where('activity_memberships.activity_id', $activityId))
            ->selectRaw(
                'activity_memberships.activity_id,
                 COALESCE(SUM(payments.amount), 0) as total_amount'
            )
            ->groupBy('activity_memberships.activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function membershipRefunds(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->join('activity_memberships', function ($join): void {
                $join->on('activity_memberships.id', '=', 'payments.payable_id')
                    ->where('payments.payable_type', ActivityMembership::class);
            })
            ->whereBetween('refunds.refunded_at', [$from, $to])
            ->when($activityId !== null, fn ($query) => $query->where('activity_memberships.activity_id', $activityId))
            ->selectRaw(
                'activity_memberships.activity_id,
                 COALESCE(SUM(refunds.amount), 0) as total_amount'
            )
            ->groupBy('activity_memberships.activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function usagePayments(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return Payment::query()
            ->join('activity_usages', function ($join): void {
                $join->on('activity_usages.id', '=', 'payments.payable_id')
                    ->where('payments.payable_type', ActivityUsage::class);
            })
            ->whereBetween('payments.paid_at', [$from, $to])
            ->when($activityId !== null, fn ($query) => $query->where('activity_usages.activity_id', $activityId))
            ->selectRaw(
                'activity_usages.activity_id,
                 COALESCE(SUM(payments.amount), 0) as total_amount'
            )
            ->groupBy('activity_usages.activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function usageRefunds(Carbon $from, Carbon $to, ?int $activityId): Collection
    {
        return Refund::query()
            ->join('payments', 'payments.id', '=', 'refunds.payment_id')
            ->join('activity_usages', function ($join): void {
                $join->on('activity_usages.id', '=', 'payments.payable_id')
                    ->where('payments.payable_type', ActivityUsage::class);
            })
            ->whereBetween('refunds.refunded_at', [$from, $to])
            ->when($activityId !== null, fn ($query) => $query->where('activity_usages.activity_id', $activityId))
            ->selectRaw(
                'activity_usages.activity_id,
                 COALESCE(SUM(refunds.amount), 0) as total_amount'
            )
            ->groupBy('activity_usages.activity_id')
            ->get()
            ->keyBy('activity_id');
    }

    private function summary(Collection $rows): array
    {
        return [
            'activitiesCount' => $rows->count(),
            'sessions' => (int) $rows->sum('sessions.total'),
            'expectedAttendance' => (int) $rows->sum('attendance.expected'),
            'present' => (int) $rows->sum('attendance.present'),
            'memberships' => (int) $rows->sum('memberships.count'),
            'newMemberships' => (int) $rows->sum('memberships.new'),
            'renewedMemberships' => (int) $rows->sum('memberships.renewed'),
            'usages' => (int) $rows->sum('usages.count'),

            'revenue' => [
                'membershipGross' => round((float) $rows->sum('revenue.memberships.gross'), 2),
                'membershipRefunds' => round((float) $rows->sum('revenue.memberships.refunds'), 2),
                'membershipNet' => round((float) $rows->sum('revenue.memberships.net'), 2),
                'activityUsageGross' => round((float) $rows->sum('revenue.activityUsages.gross'), 2),
                'activityUsageRefunds' => round((float) $rows->sum('revenue.activityUsages.refunds'), 2),
                'activityUsageNet' => round((float) $rows->sum('revenue.activityUsages.net'), 2),
                'gross' => round((float) $rows->sum('revenue.gross'), 2),
                'refunds' => round((float) $rows->sum('revenue.refunds'), 2),
                'net' => round((float) $rows->sum('revenue.net'), 2),
            ],
        ];
    }
}
