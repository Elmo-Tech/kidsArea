<?php

namespace App\Http\Resources\V1\Payslip;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => [
                'id' => $this['period']['id'],
                'year' => $this['period']['year'],
                'month' => $this['period']['month'],
                'startDate' => $this['period']['startDate'],
                'endDate' => $this['period']['endDate'],
                'status' => $this['period']['status'],
            ],

            'employee' => [
                'id' => $this['employee']['id'],
                'name' => $this['employee']['name'],
                'jobTitle' => $this['employee']['jobTitle'],
            ],

            'salary' => [
                'salaryType' => $this['salary']['salaryType'],

                'basicSalary' =>
                    $this['salary']['basicSalary'],

                'proratedBasicSalary' =>
                    $this['salary']['proratedBasicSalary'],

                'hourlyRate' =>
                    $this['salary']['hourlyRate'],

                'hourlyEarnings' =>
                    $this['salary']['hourlyEarnings'],

                'sessionRate' =>
                    $this['salary']['sessionRate'],

                'sessionEarnings' =>
                    $this['salary']['sessionEarnings'],
            ],

            'attendance' => [
                'workedMinutes' =>
                    $this['attendance']['workedMinutes'],

                'absenceDays' =>
                    $this['attendance']['absenceDays'],

                'unexcusedLateMinutes' =>
                    $this['attendance']['unexcusedLateMinutes'],

                'unexcusedEarlyLeaveMinutes' =>
                    $this['attendance']['unexcusedEarlyLeaveMinutes'],

                'attendanceDeductions' =>
                    $this['attendance']['attendanceDeductions'],
            ],

            'sessions' => [
                'completedSessions' =>
                    $this['sessions']['completedSessions'],
            ],

            'adjustments' =>
                $this['adjustments'],

            'totals' => [
                'additionsTotal' =>
                    $this['totals']['additionsTotal'],

                'deductionsTotal' =>
                    $this['totals']['deductionsTotal'],

                'grossSalary' =>
                    $this['totals']['grossSalary'],

                'netSalary' =>
                    $this['totals']['netSalary'],
            ],

            'payablePeriod' => [
                'from' => $this['payablePeriod']['from'],
                'to' => $this['payablePeriod']['to'],
                'prorationDays' =>
                    $this['payablePeriod']['prorationDays'],
            ],

            'payment' => [
                'status' => $this->payment_status,
                'paidAmount' => $this->paid_amount,

                'remainingAmount' => $this->remaining_amount
            ],
        ];
    }
}
