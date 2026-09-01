<?php

use App\Enums\CashShiftStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;

    public function up(): void
    {
        Schema::create('cash_shifts', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('cash_register_id')
                ->constrained('cash_registers')
                ->restrictOnDelete();

            $table->foreignId('opened_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->dateTime('opened_at');

            $table->decimal('opening_balance', 12, 2)
                ->default(0);

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('closed_at')
                ->nullable();

            $table->decimal('expected_closing_balance', 12, 2)
                ->nullable();

            $table->decimal('actual_closing_balance', 12, 2)
                ->nullable();

            $table->decimal('difference', 12, 2)
                ->nullable();

            $table->tinyInteger('status')
                ->default(CashShiftStatusEnum::OPEN->value);

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'cash_register_id',
                'status',
            ]);

            $table->index([
                'opened_by',
                'opened_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
