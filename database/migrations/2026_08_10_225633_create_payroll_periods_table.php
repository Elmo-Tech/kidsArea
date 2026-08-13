<?php

use App\Enums\PayrollPeriodStatusEnum;
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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');

            $table->date('start_date');
            $table->date('end_date');

            $table->tinyInteger('proration_method')
                ->default(PayrollProrationMethodEnum::FIXED_30_DAYS->value);

            $table->tinyInteger('status')
                ->default(PayrollPeriodStatusEnum::DRAFT->value);

            $table->timestamp('finalized_at')->nullable();

            $table->foreignId('finalized_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique([
                'year',
                'month',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
