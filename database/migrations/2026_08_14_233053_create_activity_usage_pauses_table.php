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
        Schema::create(
            'activity_usage_pauses',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('activity_usage_id')
                    ->constrained('activity_usages')
                    ->cascadeOnDelete();

                $table->dateTime('paused_at');

                $table->dateTime('resumed_at')
                    ->nullable();

                $table->unsignedInteger(
                    'duration_minutes'
                )->nullable();

                $table->timestamps();

                $this->createdUpdatedByRelationship($table);

                $table->index([
                    'activity_usage_id',
                    'resumed_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'activity_usage_pauses'
        );
    }
};
