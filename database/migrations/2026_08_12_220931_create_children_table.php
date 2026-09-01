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
        Schema::create('children', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 150);

            $table->date('birth_date')
                ->nullable();

            $table->tinyInteger('gender')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->string('guardian_name', 150);

            $table->string('guardian_phone', 50);

            $table->string('guardian_relation', 100)
                ->nullable();

            $table->string('guardian_email', 150)
                ->nullable();

            $table->text('guardian_notes')
                ->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index('name');
            $table->index('guardian_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
