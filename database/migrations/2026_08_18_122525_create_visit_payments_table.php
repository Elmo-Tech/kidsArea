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
        Schema::create('visit_payments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('visit_checkout_id')
                ->constrained('visit_checkouts')
                ->restrictOnDelete();

            $table->decimal('amount', 12, 2);

            $table->dateTime('paid_at');

            $table->string('reference')->nullable();

            $table->text('notes')->nullable();

            $this->createdUpdatedByRelationship($table);

            $table->timestamps();

            $table->index([
                'visit_checkout_id',
                'paid_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_payments');
    }
};
