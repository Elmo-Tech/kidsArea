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
        Schema::create('leave_payroll_policies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_type_id')
                ->constrained()
                ->onDelete('cascade');

            $table->tinyInteger('salary_type');

            $table->tinyInteger('effect');

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->unique([
                'leave_type_id',
                'salary_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_payroll_policies');
    }
};
