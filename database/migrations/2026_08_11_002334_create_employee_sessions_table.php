<?php

use App\Enums\EmployeeSessionStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('employee_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('session_date');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('title')->nullable();

            $table->tinyInteger('status')
                ->default(EmployeeSessionStatusEnum::PENDING->value);

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'employee_id',
                'session_date',
            ]);

            $table->index([
                'employee_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_sessions');
    }
};
