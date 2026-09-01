<?php

use App\Enums\ActivityUsageStatusEnum;
use App\Enums\ActivityUsageTypeEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    use CreatedUpdatedByMigration;
    public function up(): void
    {
        Schema::create('activity_usages', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('visit_id')
                ->nullable()
                ->constrained('visits')
                ->nullOnDelete();

            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            $table->string('customer_name', 150);

            $table->string('customer_phone', 50);

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->restrictOnDelete();

            $table->foreignId('activity_pricing_plan_id')
                ->constrained('activity_pricing_plans')
                ->restrictOnDelete();

            $table->tinyInteger('usage_type')
                ->default(
                    ActivityUsageTypeEnum::OPEN->value
                );

            $table->dateTime('started_at');

            $table->dateTime('ended_at')
                ->nullable();

            /*
             * Used only for FIXED_DURATION.
             *
             * Example:
             * 60  = 1 hour
             * 120 = 2 hours
             */
            $table->unsignedInteger(
                'planned_duration_minutes'
            )->nullable();

            /*
             * Initial expected ending time.
             *
             * The effective end time may move forward
             * because pauses do not consume paid time.
             */
            $table->dateTime('planned_end_at')
                ->nullable();

            /*
             * Cached totals.
             *
             * Source of truth for pauses is
             * activity_usage_pauses.
             */
            $table->unsignedInteger(
                'total_paused_minutes'
            )->default(0);

            /*
             * Actual active usage duration,
             * excluding all pause time.
             */
            $table->unsignedInteger(
                'duration_minutes'
            )->default(0);

            /*
             * Snapshot of hourly price when usage starts.
             *
             * Pricing plan changes later must not
             * affect historical usages.
             */
            $table->decimal(
                'hourly_price',
                12,
                2
            );

            /*
             * Calculated amount based on actual
             * active duration.
             */
            $table->decimal(
                'expected_amount',
                12,
                2
            )->nullable();

            /*
             * Final amount approved by the employee
             * when closing the usage.
             *
             * This is NOT payment status.
             */
            $table->decimal(
                'final_amount',
                12,
                2
            )->nullable();

            $table->tinyInteger('status')
                ->default(
                    ActivityUsageStatusEnum::ACTIVE->value
                );

            $table->foreignId('started_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $this->createdUpdatedByRelationship($table);

            $table->index([
                'visit_id',
                'status',
            ]);

            $table->index([
                'child_id',
                'activity_id',
                'status',
            ]);

            $table->index([
                'activity_id',
                'started_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'activity_usages'
        );
    }
};
