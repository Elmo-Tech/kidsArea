<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Traits\CreatedUpdatedByMigration;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('national_id')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('address')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('job_title_id')->constrained()->onDelete('cascade');
            $this->createdUpdatedByRelationship($table);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
