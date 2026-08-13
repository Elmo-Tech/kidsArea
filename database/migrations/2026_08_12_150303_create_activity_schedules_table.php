<?php

use App\Enums\ActivityScheduleStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_schedules', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            $table->string('name');

            $table->date('start_date');

            $table->date('end_date');

            $table->time('start_time');

            $table->time('end_time');

            /*
             * Array of weekdays:
             * [0, 2, 4] => Sunday, Tuesday, Thursday
             */
            $table->json('week_days');

            $table->tinyInteger('status')
                ->default(
                    ActivityScheduleStatusEnum::ACTIVE->value
                );

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index([
                'activity_id',
                'status',
            ]);

            $table->index([
                'start_date',
                'end_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_schedules');
    }
};
