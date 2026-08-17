<?php

use App\Enums\VisitStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_id')
                ->nullable()
                ->constrained('children')
                ->nullOnDelete();

            $table->dateTime('started_at');

            $table->dateTime('closed_at')
                ->nullable();

            $table->tinyInteger('status')
                ->default(
                    VisitStatusEnum::OPEN->value
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
                'child_id',
                'status',
            ]);

            $table->index([
                'started_at',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
