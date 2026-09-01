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
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('cash_register_id')
                ->constrained('cash_registers')
                ->restrictOnDelete();

            $table->foreignId('cash_shift_id')
                ->nullable()
                ->constrained('cash_shifts')
                ->restrictOnDelete();

            $table->string('title', 255);
            $table->decimal('amount', 12, 2);
            $table->dateTime('expense_at');
            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index('expense_at');
            $table->index([
                'cash_register_id',
                'cash_shift_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
