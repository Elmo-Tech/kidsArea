<?php

namespace App\Http\Resources\V1\PayrollSummary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => [
                'id' => $this['period']['id'],
                'year' => $this['period']['year'],
                'month' => $this['period']['month'],
                'status' => $this['period']['status'],
                'finalizedAt' => $this['period']['finalizedAt'],
            ],

            'employeesCount' => $this['employeesCount'],

            'salaryTypes' => [
                'monthly' => $this['salaryTypes']['monthly'],
                'hourly' => $this['salaryTypes']['hourly'],
                'session' => $this['salaryTypes']['session'],
                'monthlyPlusSession' =>
                    $this['salaryTypes']['monthlyPlusSession'],
            ],

            'earnings' => [
                'basicSalaryTotal' =>
                    $this['earnings']['basicSalaryTotal'],

                'proratedBasicSalaryTotal' =>
                    $this['earnings']['proratedBasicSalaryTotal'],

                'hourlyEarningsTotal' =>
                    $this['earnings']['hourlyEarningsTotal'],

                'sessionEarningsTotal' =>
                    $this['earnings']['sessionEarningsTotal'],

                'additionsTotal' =>
                    $this['earnings']['additionsTotal'],

                'grossSalaryTotal' =>
                    $this['earnings']['grossSalaryTotal'],
            ],

            'deductions' => [
                'attendanceDeductionsTotal' =>
                    $this['deductions']['attendanceDeductionsTotal'],

                'manualDeductionsTotal' =>
                    $this['deductions']['manualDeductionsTotal'],
            ],

            'netSalaryTotal' =>
                $this['netSalaryTotal'],

            'payments' => [
                'paidEmployeesCount' =>
                    $this['payments']['paidEmployeesCount'],

                'partiallyPaidEmployeesCount' =>
                    $this['payments']['partiallyPaidEmployeesCount'],

                'unpaidEmployeesCount' =>
                    $this['payments']['unpaidEmployeesCount'],

                'paidAmountTotal' =>
                    $this['payments']['paidAmountTotal'],

                'remainingAmountTotal' =>
                    $this['payments']['remainingAmountTotal'],
            ],
        ];
    }
}
