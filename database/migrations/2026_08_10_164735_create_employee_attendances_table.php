<?php

use App\Enums\EmployeeAttendanceStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('attendance_date');

            $table->time('check_in_at')->nullable();
            $table->time('check_out_at')->nullable();

            $table->unsignedInteger('worked_minutes')->nullable();

            $table->unsignedInteger('late_minutes')
                ->default(0);

            $table->unsignedInteger('excused_late_minutes')
                ->default(0);

            $table->unsignedInteger('early_leave_minutes')
                ->default(0);

            $table->unsignedInteger('excused_early_leave_minutes')
                ->default(0);

            $table->tinyInteger('status')
                ->default(
                    EmployeeAttendanceStatusEnum::INCOMPLETE->value
                );

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique([
                'employee_id',
                'attendance_date',
            ]);

            $table->index([
                'attendance_date',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};
