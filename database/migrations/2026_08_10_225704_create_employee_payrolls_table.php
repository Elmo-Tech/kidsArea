<?php

use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('employee_payrolls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_period_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('employee_contract_id')
                ->nullable()
                ->constrained('employee_contracts')
                ->nullOnDelete();

            // Contract snapshot
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();

            // Payroll-effective period
            $table->date('payable_from');
            $table->date('payable_to');

            $table->unsignedInteger('proration_days')->default(0);

            // Contract salary snapshot
            $table->tinyInteger('salary_type');

            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->decimal('session_rate', 12, 2)->nullable();

            // Calculated earnings
            $table->decimal('prorated_basic_salary', 12, 2)
                ->default(0);

            $table->unsignedInteger('worked_minutes')
                ->default(0);

            $table->decimal('hourly_earnings', 12, 2)
                ->default(0);

            $table->unsignedInteger('completed_sessions')
                ->default(0);

            $table->decimal('session_earnings', 12, 2)
                ->default(0);

            // Attendance impact
            $table->unsignedInteger('absence_days')
                ->default(0);

            $table->unsignedInteger('unexcused_late_minutes')
                ->default(0);

            $table->unsignedInteger('unexcused_early_leave_minutes')
                ->default(0);

            $table->decimal('attendance_deductions', 12, 2)
                ->default(0);

            // Manual adjustments
            $table->decimal('additions_total', 12, 2)
                ->default(0);

            $table->decimal('deductions_total', 12, 2)
                ->default(0);

            // Final amounts
            $table->decimal('gross_salary', 12, 2)
                ->default(0);

            $table->decimal('net_salary', 12, 2)
                ->default(0);

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique([
                'payroll_period_id',
                'employee_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payrolls');
    }
};
