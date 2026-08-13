<?php

use App\Enums\ActivityStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'activity_pricing_plans',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('activity_id')
                    ->constrained('activities')
                    ->cascadeOnDelete();

                $table->string('name');

                $table->tinyInteger('type');

                $table->decimal(
                    'price',
                    12,
                    2
                );

                $table->unsignedInteger(
                    'duration_value'
                )->nullable();

                $table->tinyInteger(
                    'duration_unit'
                )->nullable();

                $table->unsignedInteger(
                    'sessions_count'
                )->nullable();

                $table->tinyInteger('status')
                    ->default(
                        ActivityStatusEnum::ACTIVE->value
                    );

                $table->text('notes')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'activity_id',
                    'status',
                ]);

                $table->index([
                    'activity_id',
                    'type',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'activity_pricing_plans'
        );
    }
};
