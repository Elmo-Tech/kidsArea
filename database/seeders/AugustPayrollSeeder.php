<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PayrollPeriodStatusEnum;
use App\Models\PayrollPeriod;
use App\Models\PayrollSetting;
use App\Services\Payroll\PayrollPeriodService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AugustPayrollSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $settings = PayrollSetting::query()->firstOrCreate([]);

            $period = PayrollPeriod::query()->updateOrCreate(
                [
                    'year' => 2026,
                    'month' => 8,
                ],
                [
                    'start_date' => '2026-08-01',
                    'end_date' => '2026-08-31',
                    'proration_method' => $settings->proration_method->value,
                    'status' => PayrollPeriodStatusEnum::DRAFT->value,
                    'finalized_by' => null,
                    'finalized_at' => null,
                    'notes' => 'Test payroll for August 2026',
                ]
            );

            app(PayrollPeriodService::class)
                ->generatePayroll($period);
        });
    }
}
