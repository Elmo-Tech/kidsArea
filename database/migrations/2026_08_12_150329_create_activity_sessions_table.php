<?php

use App\Enums\ActivitySessionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_sessions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            $table->foreignId('activity_schedule_id')
                ->nullable()
                ->constrained('activity_schedules')
                ->nullOnDelete();

            $table->date('session_date');

            $table->time('start_time');

            $table->time('end_time');

            $table->string('title')
                ->nullable();

            $table->tinyInteger('status')
                ->default(
                    ActivitySessionStatusEnum::SCHEDULED->value
                );

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'activity_id',
                'session_date',
            ]);

            $table->index([
                'activity_schedule_id',
                'session_date',
            ]);

            $table->index([
                'session_date',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_sessions');
    }
};
