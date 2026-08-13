<?php

use App\Enums\PayrollDeductionMethodEnum;
use App\Enums\PayrollProrationMethodEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();

            $table->tinyInteger('proration_method')
                ->default(
                    PayrollProrationMethodEnum::FIXED_30_DAYS->value
                );

            $table->tinyInteger('late_deduction_method')
                ->default(
                    PayrollDeductionMethodEnum::BY_MINUTE->value
                );

            $table->tinyInteger('early_leave_deduction_method')
                ->default(
                    PayrollDeductionMethodEnum::BY_MINUTE->value
                );

            $table->tinyInteger('absence_deduction_method')
                ->default(
                    PayrollDeductionMethodEnum::BY_DAY->value
                );

            $table->boolean('block_finalize_on_incomplete_attendance')
                ->default(true);

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
