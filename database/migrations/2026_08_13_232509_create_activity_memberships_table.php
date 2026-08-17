<?php

use App\Enums\ActivityMembershipStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    public function up(): void
    {
        Schema::create('activity_memberships', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_id')
                ->constrained('children')
                ->cascadeOnDelete();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->cascadeOnDelete();

            $table->foreignId('activity_pricing_plan_id')
                ->constrained('activity_pricing_plans')
                ->restrictOnDelete();

            $table->date('start_date');

            $table->date('end_date')
                ->nullable();

            $table->unsignedInteger('sessions_total')
                ->nullable();

            /*
             * Snapshot of the price at purchase time.
             *
             * If the pricing plan changes later,
             * old memberships keep their original price.
             */
            $table->decimal('price', 12, 2);

            $table->tinyInteger('status')
                ->default(
                    ActivityMembershipStatusEnum::ACTIVE->value
                );

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $this->createdUpdatedByRelationship($table);

            $table->index([
                'child_id',
                'activity_id',
                'status',
            ]);

            $table->index([
                'activity_id',
                'start_date',
                'end_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_memberships');
    }
};
