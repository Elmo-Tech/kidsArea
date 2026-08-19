<?php

namespace Database\Seeders;

use App\Enums\PayrollDeductionMethodEnum;
use App\Enums\PayrollProrationMethodEnum;
use App\Models\PayrollSetting;
use Illuminate\Database\Seeder;

class PayrollSettingSeeder extends Seeder
{
    public function run(): void
    {
        PayrollSetting::query()->firstOrCreate(
            [],
            [
                'proration_method' =>
                    PayrollProrationMethodEnum::FIXED_30_DAYS->value,

                'late_deduction_method' =>
                    PayrollDeductionMethodEnum::BY_MINUTE->value,

                'early_leave_deduction_method' =>
                    PayrollDeductionMethodEnum::BY_MINUTE->value,

                'absence_deduction_method' =>
                    PayrollDeductionMethodEnum::BY_DAY->value,

                'block_finalize_on_incomplete_attendance' =>
                    true,
            ]
        );
    }
}
