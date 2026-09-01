<?php

use App\Enums\ActivitySessionEmployeeAttendanceStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('activity_session_employee_attendances', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('activity_session_id');

            $table->foreign(
                'activity_session_id',
                'asea_session_fk'
            )
                ->references('id')
                ->on('activity_sessions')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('employee_id');

            $table->foreign(
                'employee_id',
                'asea_employee_fk'
            )
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();

            $table->unsignedInteger('late_minutes')->default(0);

            $table->tinyInteger('status')
                ->default(ActivitySessionEmployeeAttendanceStatusEnum::PRESENT->value);

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique([
                'activity_session_id',
                'employee_id',
            ], 'activity_session_id_employee_unique');

            $table->index([
                'employee_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_session_employee_attendances');
    }
};
