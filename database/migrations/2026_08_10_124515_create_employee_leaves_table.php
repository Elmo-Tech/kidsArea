<?php

use App\Enums\EmployeeLeaveStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('leave_type_id')
                ->constrained()
                ->onDelete('restrict');

            $table->date('start_date');
            $table->date('end_date');

            $table->unsignedInteger('days_count');

            $table->string('reason')->nullable();

            $table->tinyInteger('status')
                ->default(EmployeeLeaveStatusEnum::PENDING->value);

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'employee_id',
                'start_date',
                'end_date',
            ]);

            $table->index([
                'employee_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leaves');
    }
};
