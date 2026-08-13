<?php

use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_payments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('employee_payroll_id')
                ->constrained('employee_payrolls')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->dateTime('paid_at');

            $table->string('reference', 255)
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $this->createdUpdatedByRelationship($table);

            $table->index([
                'employee_payroll_id',
                'paid_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_payments');
    }
};
