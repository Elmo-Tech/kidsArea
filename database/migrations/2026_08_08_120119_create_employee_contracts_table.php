<?php

use App\Enums\ContractStatusEnum;
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
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->tinyInteger('salary_type');

            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->decimal('session_rate', 12, 2)->nullable();

            $table->decimal('required_monthly_hours', 8, 2)->nullable();

            $table->time('work_start_time')->nullable();
            $table->time('work_end_time')->nullable();

            $table->json('work_days')->nullable();

            $table->tinyInteger('status')
                ->default(ContractStatusEnum::ACTIVE->value);

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'employee_id',
                'status',
            ]);

            $table->index([
                'start_date',
                'end_date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
