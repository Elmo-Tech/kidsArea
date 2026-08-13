<?php

use App\Enums\EmployeePermissionStatusEnum;
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
        Schema::create('employee_permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('permission_date');

            $table->tinyInteger('type');

            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();

            $table->unsignedInteger('minutes')->nullable();

            $table->string('reason')->nullable();

            $table->tinyInteger('status')
                ->default(EmployeePermissionStatusEnum::PENDING->value);

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
                'permission_date',
            ]);

            $table->index([
                'employee_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_permissions');
    }
};
