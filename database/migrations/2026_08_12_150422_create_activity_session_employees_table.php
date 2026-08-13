<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'activity_session_employees',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('activity_session_id')
                    ->constrained('activity_sessions')
                    ->cascadeOnDelete();

                $table->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique([
                    'activity_session_id',
                    'employee_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'activity_session_employees'
        );
    }
};
