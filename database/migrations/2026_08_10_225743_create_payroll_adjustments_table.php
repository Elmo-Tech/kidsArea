<?php

use App\Enums\PayrollAdjustmentTypeEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_payroll_id')
                ->constrained()
                ->onDelete('cascade');

            $table->tinyInteger('type');

            $table->decimal('amount', 12, 2);

            $table->string('reason');

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'employee_payroll_id',
                'type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
